<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\BillProduct;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\Mail\BillPaymentCreate;
use App\Models\Mail\BillSend;
use App\Models\Mail\VenderBillSend;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\StockReport;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Utility;
use App\Models\Vender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BillExport;
use App\Models\BillAccount;
use App\Models\ChartOfAccount;
use App\Models\DebitNote;
use App\Models\Status;
use App\Models\TransactionLines;
use Exception;
use Carbon\Carbon;
use CoinGate\Exception\Api\BadRequest;
use NumberToWords\NumberToWords;

class BillController extends Controller
{

    public function index(Request $request)
    {
        if (\Auth::user()->can('manage bill')) {

            $vender = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $vender->prepend('Select Vendor', '');

            $status = Status::getAllAsArray();

            $query = Bill::where('created_by', '=', \Auth::user()->creatorId());
            if (!empty($request->vender)) {
                $query->where('vender_id', '=', $request->vender);
            }


            if (str_contains($request->bill_date, ' to ')) {
                $date_range = explode(' to ', $request->bill_date);
                $query->whereBetween('bill_date', $date_range);
            } elseif (!empty($request->bill_date)) {

                $query->where('bill_date', $request->bill_date);
            }

            // if (!empty($request->bill_date)) {
            //     $date_range = explode(' to ', $request->bill_date);
            //     $query->whereBetween('bill_date', $date_range);
            // }

            if (!empty($request->status)) {
                $query->where('status', '=', $request->status);
            }
            $bills = $query->get();

            return view('bill.index', compact('bills', 'vender', 'status'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function create($vendorId)
    {

        if (\Auth::user()->can('create bill')) {
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'bill')->get();
            $category     = ProductServiceCategory::where('created_by', \Auth::user()->creatorId())->where('type', 'expense')->orderBy('name', 'asc')->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');

            $bill_number = \Auth::user()->billNumberFormat($this->billNumber());
            $venders     = Vender::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $venders->prepend('Select Vendor', '');

            $product_services = ProductService::where('created_by', \Auth::user()->creatorId())
                ->orderBy('name', 'asc')
                ->get()
                ->pluck('name', 'id')
                ->prepend('Select Item', '');
            $product_services->prepend('Select Item', '');

            $chartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
                ->where('created_by', \Auth::user()->creatorId())->get()
                ->pluck('code_name', 'id');
            $chartAccounts->prepend('Select Account', '');

            $subAccounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_account_parents.account');
            $subAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
            $subAccounts->where('chart_of_accounts.parent', '!=', 0);
            $subAccounts->where('chart_of_accounts.created_by', \Auth::user()->creatorId());
            $subAccounts = $subAccounts->get()->toArray();

            return view('bill.create', compact('venders', 'bill_number', 'product_services', 'category', 'customFields', 'vendorId', 'chartAccounts', 'subAccounts'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function store(Request $request)
    {
        // return response()->json(['error' => __('Permission denied.')], 401);

        if (\Auth::user()->can('create bill')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'vender_id' => 'required',
                    'bill_date' => 'required',
                    'due_date'  => 'required',
                    'items'     => 'required|array|min:1',
                    'items.*.item'         => 'required|integer',
                    'items.*.quantity'     => 'required|numeric|min:0.0001',
                    'items.*.price'        => 'required|numeric|min:0',
                    'items.*.discount'     => 'nullable|numeric|min:0',
                    'items.*.itemTaxPrice' => 'nullable|numeric|min:0',
                    // si te la envían por cada ítem:
                    'items.*.category_id'  => 'nullable|integer',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $bill            = new Bill();
            $bill->bill_id   = $this->billNumber();
            $bill->vender_id = $request->vender_id;
            $bill->bill_date      = $request->bill_date;
            // $bill->status         = $request->estatus_id;
            $bill->status         = 0;
            $bill->due_date       = $request->due_date;

            $bill->order_number   = !empty($request->order_number) ? $request->order_number : 0;
            $bill->discount_apply = isset($request->discount_apply) ? 1 : 0;
            $bill->created_by     = \Auth::user()->creatorId();
            $bill->save();            
            Utility::starting_number($bill->bill_id + 1, 'bill');
            if (!empty($request->customField)){
                CustomField::saveData($bill, $request->customField);
            }
            $products = $request->items;

            $total_amount = 0;

            $total_amount = 0;

            for ($i = 0; $i < count($products); $i++) {

                $itemCategoryId = $products[$i]['category_id'] ?? null;

                // Guarda la línea de productos
                $billProduct              = new BillProduct();
                $billProduct->bill_id     = $bill->id;
                $billProduct->product_id  = $products[$i]['item'];
                $billProduct->quantity    = $products[$i]['quantity'];
                $billProduct->tax         = $products[$i]['tax'];
                $billProduct->discount    = $products[$i]['discount'] ?? 0;
                $billProduct->price       = $products[$i]['price'];
                $billProduct->description = $products[$i]['description'] ?? null;
                $billProduct->category_id = $itemCategoryId ?? null;
                $billProduct->save();

                // >>> Cálculo del total de la línea
                $qty         = (float) $billProduct->quantity;
                $price       = (float) $billProduct->price;
                $discount    = (float) ($billProduct->discount ?? 0);
                $taxAmount   = (float) ($products[$i]['itemTaxPrice'] ?? 0); // ya viene calculado
                $lineBase    = max(0, ($qty * $price) - $discount);
                $lineTotal   = $lineBase + $taxAmount;

                // >>> Determinar la categoría del ítem (prioriza la que viene en el request)
                // si no viene, toma la del ProductService

                if (!$itemCategoryId) {
                    $ps = \App\Models\ProductService::select('id', 'category_id')->find($billProduct->product_id);
                    if ($ps && $ps->category_id) {
                        $itemCategoryId = $ps->category_id;
                    }
                }

                // >>> Crear BillAccount por categoría del ítem
                if (!empty($itemCategoryId)) {
                    $itemCategory = \App\Models\ProductServiceCategory::find($itemCategoryId);
                    if ($itemCategory && !empty($itemCategory->chart_account_id)) {
                        $ba                    = new \App\Models\BillAccount();
                        $ba->chart_account_id  = $itemCategory->chart_account_id;
                        $ba->price             = $lineTotal; // monto de la línea (base - desc + impuesto)
                        $ba->description       = $billProduct->description
                            ?: ('Línea de factura - ProdID: ' . $billProduct->product_id);
                        $ba->type              = 'Bill Item Category'; // etiqueta clara para auditoría
                        $ba->ref_id            = $bill->id;
                        $ba->save();
                    }
                }

                // >>> (Opcional) Si sigues permitiendo chart_account_id por línea, se mantiene:
                if (!empty($products[$i]['chart_account_id'])) {
                    $ba2                    = new \App\Models\BillAccount();
                    $ba2->chart_account_id  = $products[$i]['chart_account_id'];
                    // Si te pasan un "amount" manual, úsalo; si no, usa el lineTotal
                    $ba2->price             = isset($products[$i]['amount']) && is_numeric($products[$i]['amount'])
                        ? (float) $products[$i]['amount']
                        : $lineTotal;
                    $ba2->description       = $billProduct->description
                        ?: ('Asiento manual por línea - ProdID: ' . $billProduct->product_id);
                    $ba2->type              = 'Bill Item Manual';
                    $ba2->ref_id            = $bill->id;
                    $ba2->save();
                }

                // Stock y reporte
                if (!empty($products[$i]['item'])) {
                    $type        = 'bill';
                    $type_id     = $bill->id;
                    $description = $billProduct->quantity . ' ' . __('quantity purchase in bill') . ' ' . \Auth::user()->billNumberFormat($bill->bill_id);
                    Utility::addProductStock($products[$i]['item'], $billProduct->quantity, $type, $description, $type_id);
                }

                // Acumula total de la factura (usa siempre el total calculado de la línea)
                $total_amount += $lineTotal;
            }

            if (!empty($request->chart_account_id)) {

                $billaccount = ProductServiceCategory::find($request->category_id);
                $chart_account = ChartOfAccount::find($billaccount->chart_account_id);
                $billAccount                    = new BillAccount();
                $billAccount->chart_account_id  = $chart_account['id'];
                $billAccount->price             = $total_amount;
                $billAccount->description       = $request->description;
                $billAccount->type              = 'Bill Category';
                $billAccount->ref_id            = $bill->id;
                $billAccount->save();
            }


            //Twilio Notification
            $setting  = Utility::settings(\Auth::user()->creatorId());
            $billId    = Crypt::encrypt($bill->id);
            $bill->url = route('bill.pdf', $billId);
            $vendor = Vender::find($request->vender_id);
            if (isset($setting['bill_notification']) && $setting['bill_notification'] == 1) {
                $uArr = [
                    'bill_name' => $vendor->name,
                    'bill_number'  => \Auth::user()->billNumberFormat($bill->bill_id),
                    'bill_url'  =>  $bill->url,
                ];
                Utility::send_twilio_msg($vendor->contact, 'new_bill', $uArr);
            }

            // webhook
            $module = 'New Bill';
            $webhook =  Utility::webhookSetting($module);
            if ($webhook) {
                $parameter = json_encode($bill);
                // 1 parameter is  URL , 2 parameter is data , 3 parameter is method
                $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                if ($status == true) {
                    return redirect()->route('bill.index', $bill->id)->with('success', __('Bill successfully created.'));
                } else {
                    return redirect()->back()->with('error', __('Webhook call failed.'));
                }
            }

            return redirect()->route('bill.index', $bill->id)->with('success', __('Bill successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    function venderNumber()
    {
        $latest = Vender::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if (!$latest) {
            return 1;
        }

        return $latest->customer_id + 1;
    }

    public function show($ids)
    {

        if (\Auth::user()->can('show bill')) {
            try {
                $id       = Crypt::decrypt($ids);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Bill Not Found.'));
            }

            $id   = Crypt::decrypt($ids);
            $bill = Bill::with('debitNote', 'payments.bankAccount', 'items.product.unit')->find($id);

            if (!empty($bill) && $bill->created_by == \Auth::user()->creatorId()) {
                $billPayment = BillPayment::where('bill_id', $bill->id)->first();
                $vendor      = $bill->vender;

                $item      = $bill->items;
                $accounts  = $bill->accounts;
                $items     = [];
                if (!empty($item) && count($item) > 0) {
                    foreach ($item as $k => $val) {
                        if (!empty($accounts[$k])) {
                            $val['chart_account_id'] = $accounts[$k]['chart_account_id'];
                            $val['account_id'] = $accounts[$k]['id'];
                            $val['amount'] = $accounts[$k]['price'];
                        }
                        $items[] = $val;
                    }
                } else {

                    foreach ($accounts as $k => $val) {
                        $val1['chart_account_id'] = $accounts[$k]['chart_account_id'];
                        $val1['account_id'] = $accounts[$k]['id'];
                        $val1['amount'] = $accounts[$k]['price'];
                        $items[] = $val1;
                    }
                }

                $bill->customField = CustomField::getData($bill, 'bill');
                $customFields      = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'bill')->get();

                return view('bill.view', compact('bill', 'vendor', 'items', 'billPayment', 'customFields'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function edit($ids)
    {
        
        if (\Auth::user()->can('edit bill')) {
            $id       = Crypt::decrypt($ids);
            $bill     = Bill::find($id);
            $category = ProductServiceCategory::where('created_by', \Auth::user()->creatorId())->where('type', 'expense')->orderBy('name', 'asc')->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');

            $bill_number      = \Auth::user()->billNumberFormat($bill->bill_id);
            $venders          = Vender::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            $estatus = Status::getAllAsArray();

            $product_services = ProductService::where('created_by', \Auth::user()->creatorId())
                ->orderBy('name', 'asc')
                ->get()
                ->pluck('name', 'id');

            $bill->customField = CustomField::getData($bill, 'bill');
            $customFields      = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'bill')->get();

            $chartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
                ->where('created_by', \Auth::user()->creatorId())->get()
                ->pluck('code_name', 'id');
            $chartAccounts->prepend('Select Account', '');

            $subAccounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_account_parents.account');
            $subAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
            $subAccounts->where('chart_of_accounts.parent', '!=', 0);
            $subAccounts->where('chart_of_accounts.created_by', \Auth::user()->creatorId());
            $subAccounts = $subAccounts->get()->toArray();

            //for item and account show in repeater
            $item      = $bill->items;
            $accounts  = $bill->accounts;
            $items     = [];
            if (!empty($item) && count($item) > 0) {
                foreach ($item as $k => $line) {
                    // Convierte a array para manipular fácilmente
                    $row = $line->toArray();

                    // category_id de la línea; si no tiene, usa la del producto
                    if (empty($row['category_id'])) {
                        $row['category_id'] = ProductService::where('id', $line->product_id)->value('category_id');
                    }

                    // Mantén tu lógica de cuentas por índice (si aplica)
                    if (!empty($accounts[$k])) {
                        $row['chart_account_id'] = $accounts[$k]['chart_account_id'];
                        $row['account_id']       = $accounts[$k]['id'];
                        $row['amount']           = $accounts[$k]['price'];
                    }
                    
                    $items[] = $row;
                }
            } else {
                foreach ($accounts as $k => $acc) {
                    $row = [
                        'chart_account_id' => $acc['chart_account_id'],
                        'account_id'       => $acc['id'],
                        'amount'           => $acc['price'],
                        // cuando no hay líneas, no hay product_id/category_id a inferir
                    ];
                    $items[] = $row;
                }
            }

            return view('bill.edit', compact('venders', 'product_services', 'bill', 'bill_number', 'category', 'customFields', 'chartAccounts', 'items', 'subAccounts', 'estatus'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Bill $bill)
    {
        if (!\Auth::user()->can('edit bill')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($bill->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'vender_id' => 'required|integer',
                'bill_date' => 'required|date',
                'due_date'  => 'required|date',
                'items'     => 'required|array|min:1',
            ],
            [
                'required' => 'El :attribute campo es requerido'
            ],
            [
                'vender' => 'proveedor',
                'bill_date' => 'fecha de factura',
                'due_date' => 'fecha de vencimiento',
                'items' => 'producto',
            ]
        );

        if ($validator->fails()) {
            
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
                
            // return redirect()->route('bill.index')->with('error', $validator->getMessageBag()->first());
        }        

        // 1) Actualiza solo datos "cabezales" (NO status)
        $bill->vender_id    = $request->vender_id;
        $bill->bill_date    = $request->bill_date;
        $bill->due_date     = $request->due_date;
        $bill->order_number = $request->order_number;
        $bill->status    = $request->estatus_id; // ← INTENCIONALMENTE NO SE TOCA
        $bill->save();

        // Campos personalizados
        if (!empty($request->customField)){
            CustomField::saveData($bill, $request->customField);
        }

        $products = $request->items;
        $inventoryChanged = false;     // si hay cambios reales (no solo categoría)
        $onlyCategoryChanges = true;   // asumimos que solo cambian categorías hasta probar lo contrario

        foreach ($products as $row) {
            $lineId = isset($row['id']) ? (int)$row['id'] : 0;
            $existing = $lineId > 0 ? BillProduct::find($lineId) : null;
            
            // Valores nuevos propuestos
            $newProductId = isset($row['items']) ? (int)$row['items'] : ($existing?->product_id);
            $newQty       = isset($row['quantity']) ? (float)$row['quantity'] : ($existing?->quantity ?? 0);
            $newPrice     = isset($row['price']) ? (float)$row['price'] : ($existing?->price ?? 0);
            $newTax       = $row['tax'] ?? ($existing?->tax);
            $newDiscount  = isset($row['discount']) ? (float)$row['discount'] : ($existing?->discount ?? 0);
            $newDesc      = $row['description'] ?? ($existing?->description);
            $newCatId     = $row['category_id'] ?? ($existing?->category_id);            
            
            if (is_null($existing)) {
                // 2) Línea nueva → sí mueve inventario (comportamiento normal)
                $bp                 = new BillProduct();
                $bp->bill_id        = $bill->id;
                $bp->product_id     = $newProductId;
                $bp->quantity       = $newQty;
                $bp->tax            = $newTax;
                $bp->discount       = $newDiscount;
                $bp->price          = $newPrice;
                $bp->description    = $newDesc;
                $bp->category_id    = $newCatId ?? null;
                $bp->save();

                // Ajuste inventario
                if (!empty($newProductId) && $newQty > 0) {
                    Utility::total_quantity('plus', $newQty, $newProductId);
                    $inventoryChanged = true;
                    $onlyCategoryChanges = false;
                }

                // (NO movimientos contables en EDIT)
                continue;
            }

            // 3) Línea existente: detecta si el ÚNICO cambio es la categoría
            $isOnlyCategoryChange =
                ($existing->product_id === $newProductId) &&
                ((float)$existing->quantity === (float)$newQty) &&
                ((float)$existing->price === (float)$newPrice) &&
                ((string)$existing->tax === (string)$newTax) &&
                ((float)$existing->discount === (float)$newDiscount) &&
                ((string)$existing->description === (string)$newDesc) &&
                ((int)$existing->category_id !== (int)$newCatId);

            if ($isOnlyCategoryChange) {
                // → No muevas inventario ni contabilidad: solo actualiza la categoría (y descripción si vino)
                $existing->category_id = $newCatId ?? null;
                $existing->description = $newDesc;
                $existing->save();
                // Mantén flags: solo-categoría
                continue;
            }

            // 4) Cambio REAL (producto, qty, price, tax, discount o descripción)
            $onlyCategoryChanges = false;

            // Revertir inventario previo de la línea
            if (!empty($existing->product_id) && $existing->quantity > 0) {
                Utility::total_quantity('minus', (float)$existing->quantity, (int)$existing->product_id);
            }

            // Actualizar la línea
            $existing->product_id  = $newProductId;
            $existing->quantity    = $newQty;
            $existing->tax         = $newTax;
            $existing->discount    = $newDiscount;
            $existing->price       = $newPrice;
            $existing->description = $newDesc;
            $existing->category_id = $newCatId ?? null;
            $existing->save();

            // Aplicar inventario nuevo
            if (!empty($newProductId) && $newQty > 0) {
                Utility::total_quantity('plus', $newQty, $newProductId);
                $inventoryChanged = true;
            }

            // (NO movimientos contables en EDIT)

            if (!empty($row['chart_account_id'])) {
                $ba2                    = new \App\Models\BillAccount();
                $ba2->chart_account_id  = $row['chart_account_id'];
                // Si te pasan un "amount" manual, úsalo; si no, usa el lineTotal
                $ba2->price             = isset($row['amount']) && is_numeric($row['amount'])
                    ? (float) $row['amount']
                    : 0;
                $ba2->description       = $newDesc
                    ?: ('Asiento manual por línea - ProdID: ' . $newProductId);
                $ba2->type              = 'Bill Item Manual';
                $ba2->ref_id            = $bill->id;
                $ba2->save();
            }
        }

        // 5) Si hubo cambios reales, reconstruye el StockReport de la factura UNA sola vez
        if ($inventoryChanged) {
            StockReport::where('type', 'bill')->where('type_id', $bill->id)->delete();

            $currLines = BillProduct::where('bill_id', $bill->id)->get();
            foreach ($currLines as $bp) {
                if (!empty($bp->product_id) && $bp->quantity > 0) {
                    $desc = $bp->quantity . ' ' . __('quantity purchase in bill') . ' ' . \Auth::user()->billNumberFormat($bill->bill_id);
                    Utility::addProductStock($bp->product_id, $bp->quantity, 'bill', $desc, $bill->id);
                }
            }
        }
        // Si fueron SOLO cambios de categoría, no tocamos StockReport ni inventario.

        // 6) NO crear/actualizar BillAccount ni TransactionLines en EDIT
        //    (eliminar cualquier lógica previa relacionada)
        // TransactionLines::where(...)->delete(); // ← ya NO
        // (NO Utility::addTransactionLines)

        return redirect()->route('bill.index')->with('success', __('Bill successfully updated.'));
    }



    public function destroy(Bill $bill)
    {
        if (\Auth::user()->can('delete bill')) {
            if ($bill->created_by == \Auth::user()->creatorId()) {
                $billpayments = $bill->payments;

                foreach ($billpayments as $key => $value) {
                    Utility::bankAccountBalance($value->account_id, $value->amount, 'credit');
                    $transaction = Transaction::where('payment_id', $value->id)->first();
                    $transaction->delete();

                    $billpayment = BillPayment::find($value->id)->first();
                    $billpayment->delete();
                }
                $bill->delete();

                if ($bill->vender_id != 0 && $bill->status != 0) {
                    Utility::updateUserBalance('vendor', $bill->vender_id, $bill->getDue(), 'credit');
                }
                BillProduct::where('bill_id', '=', $bill->id)->delete();

                DebitNote::where('bill', '=', $bill->id)->delete();

                TransactionLines::where('reference_id', $bill->id)->where('reference', 'Bill')->delete();
                TransactionLines::where('reference_id', $bill->id)->where('reference', 'Bill Account')->delete();
                TransactionLines::where('reference_id', $bill->id)->where('reference', 'Bill Payment')->delete();

                return redirect()->route('bill.index')->with('success', __('Bill successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    function billNumber()
    {
        $latest = Bill::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if (!$latest) {
            return 1;
        }

        return $latest->bill_id + 1;
    }

    public function product(Request $request)
    {
        try {
            $data['product']     = $product = ProductService::find($request->product_id);
            $data['unit']        = !empty($product->unit) ? $product->unit->name : 0;
            $data['taxRate']     = $taxRate = !empty($product->tax_id) ? $product->taxRate($product->tax_id) : 0;
            $data['taxes']       = !empty($product->tax_id) ? $product->tax($product->tax_id) : 0;
            $salePrice           = $product->purchase_price ?? 0;
            $quantity            = 1;
            $taxPrice            = ($taxRate / 100) * ($salePrice * $quantity) ?? 0;
            $data['totalAmount'] = ($salePrice * $quantity) ?? 0;
            return json_encode($data);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => __('Something went wrong.')]);
        }
    }

    public function productDestroy(Request $request)
    {

        if (\Auth::user()->can('delete bill product')) {
            $billProduct = BillProduct::find($request->id);
            if ($billProduct) {
                $bill = Bill::find($billProduct->bill_id);
                Utility::updateUserBalance('vendor', $bill->vender_id, $request->amount, 'credit');
                $productService = ProductService::find($billProduct->product_id);
                TransactionLines::where('reference_sub_id', $productService->id)->where('reference', 'Bill')->delete();
                BillProduct::where('id', '=', $request->id)->delete();
                BillAccount::where('id', '=', $request->account_id)->delete();
                return response()->json(['status' => true, 'message' => __('Bill product successfully deleted.')]);
            } else {
                return response()->json(['status' => false, 'message' => __('Bill product not found.')]);
            }
        } else {
            return response()->json(['status' => false, 'message' => __('Permission denied.')]);
        }
    }

   public function sent($id)
{
    if (!\Auth::user()->can('send bill')) {
        return redirect()->back()->with('error', __('Permission denied.'));
    }

    $bill = Bill::findOrFail($id);

    try {
        \DB::transaction(function () use ($bill) {

            $bill->send_date = now()->toDateString();
            $bill->status    = 1;
            $bill->save();

            $vender = Vender::find($bill->vender_id);

            $bill->name = $vender?->name ?? '';
            $bill->bill = \Auth::user()->billNumberFormat($bill->bill_id);
            $bill->url  = route('bill.pdf', Crypt::encrypt($bill->id));

            // Ajusta saldo del proveedor
            Utility::updateUserBalance('vendor', $bill->vender_id, $bill->getTotal(), 'debit');

            // Evita duplicados si reenvías
            TransactionLines::where('reference_id', $bill->id)
                ->whereIn('reference', ['Bill', 'Bill Account'])
                ->delete();

            // ==== Transacciones por LÍNEA usando la CATEGORÍA ACTUAL ====
            $bill_products = BillProduct::where('bill_id', $bill->id)->get();

            foreach ($bill_products as $bp) {
                // Calcula impuestos de la línea
                $totalTaxPrice = 0;
                if (!empty($bp->tax)) {
                    $taxes = \App\Models\Utility::tax($bp->tax);
                    foreach ($taxes as $tax) {
                        $totalTaxPrice += \App\Models\Utility::taxRate(
                            $tax->rate,
                            $bp->price,
                            $bp->quantity,
                            $bp->discount
                        );
                    }
                }

                $itemAmount = ($bp->price * $bp->quantity) - ($bp->discount ?? 0) + $totalTaxPrice;

                // RESOLVER CUENTA: 1) categoría de la línea  2) fallback a cuenta del producto
                $accountId = null;

                if (!empty($bp->category_id)) {
                    $cat = ProductServiceCategory::select('chart_account_id')->find($bp->category_id);
                    $accountId = $cat?->chart_account_id;
                }

                if (empty($accountId)) {
                    $product = ProductService::select('expense_chartaccount_id')->find($bp->product_id);
                    $accountId = $product?->expense_chartaccount_id;
                }

                if (!empty($accountId)) {
                    Utility::addTransactionLines([
                        'account_id'         => $accountId,
                        'transaction_type'   => 'Debit',
                        'transaction_amount' => $itemAmount,
                        'reference'          => 'Bill',
                        'reference_id'       => $bill->id,
                        'reference_sub_id'   => $bp->product_id,
                        'date'               => $bill->bill_date,
                    ]);
                }
            }

            // ==== OPCIONAL: Solo asientos MANUALES desde BillAccount (evita duplicar lo de categoría) ====
            /*
            $manualAccounts = BillAccount::where('ref_id', $bill->id)
                ->where('type', 'Bill Item Manual')
                ->get();

            foreach ($manualAccounts as $ba) {
                Utility::addTransactionLines([
                    'account_id'         => $ba->chart_account_id,
                    'transaction_type'   => 'Debit',
                    'transaction_amount' => $ba->price,
                    'reference'          => 'Bill Account',
                    'reference_id'       => $ba->ref_id,
                    'reference_sub_id'   => $ba->id,
                    'date'               => $bill->bill_date,
                ]);
            }
            */

            // Envío de correo (si hay email)
            if ($vender && !empty($vender->email)) {
                $uArr = [
                    'bill_name'   => $bill->name,
                    'bill_number' => $bill->bill,
                    'bill_url'    => $bill->url,
                ];
                Utility::sendEmailTemplate('bill_sent', [$vender->id => $vender->email], $uArr);
            }
        });

        return redirect()->back()->with('success', __('Bill successfully sent.'));
    } catch (\Exception $e) {
        $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
        return redirect()->back()->with('error', $smtp_error . ' | ' . $e->getMessage());
    }
}


    public function resent($id)
    {

        if (\Auth::user()->can('send bill')) {
            $bill = Bill::where('id', $id)->first();

            $vender = Vender::where('id', $bill->vender_id)->first();

            $bill->name = !empty($vender) ? $vender->name : '';
            $bill->bill = \Auth::user()->billNumberFormat($bill->bill_id);

            $billId    = Crypt::encrypt($bill->id);
            $bill->url = route('bill.pdf', $billId);

            $uArr = [
                'bill_name' => $bill->name,
                'bill_number' => $bill->bill,
                'bill_url' => $bill->url,
            ];
            try {
                $resp = Utility::sendEmailTemplate('bill_sent', [$vender->id => $vender->email], $uArr);
            } catch (\Exception $e) {
                $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
            }

            return redirect()->back()->with('success', __('Bill successfully sent.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    function dineroATexto($numero)
    {
        $numberToWords = new NumberToWords();

        // Convertir parte entera a palabras
        $numberTransformer = $numberToWords->getNumberTransformer('es');
        $parteEntera = floor($numero);
        $centavos = round(($numero - $parteEntera) * 100);

        $montoEnLetras = strtoupper($numberTransformer->toWords($parteEntera));

        return "{$montoEnLetras} PESOS CON {$centavos}/100";
    }

    public function sendEmailAuth($id)
    {
        try {

            $bill = Bill::with('debitNote', 'payments.bankAccount', 'items.product.unit')->find($id);
            $vendor      = $bill->vender;

            $uArr = [
                'dirigidoA' => $vendor->billing_name,
                'montoTotal' => \Auth::user()->priceFormat($bill->getAccountTotal()),
                'montoTotalTexto' => $this->dineroATexto($bill->getAccountTotal()),
                'nombreSRL' => $vendor->shipping_name,
                'fecha_venta' => Carbon::now()->locale('es')->isoFormat('D [de] MMMM YYYY'),
                'numero_factura' => \Auth::user()->billNumberFormat($bill->bill_id),
                'numero_orden' => $bill->order_number,
                'detalle' => $bill->items[0]->product['name'],
                'ncf' => '',

            ];
            $resp = Utility::sendEmailTemplateWithDocument('bill_sent', env("MAIL_TO"), $uArr);
            return response()->json(['success' => true, 'msg' => $resp]);
        } catch (\Exception $e) {
            $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
            return response()->json(['success' => false]);
        }
    }

    public function sendEmailAuthAproved($id)
    {
        try {
            $bill = Bill::with('debitNote', 'payments.bankAccount', 'items.product.unit')->find($id);
            $vendor      = $bill->vender;

            $total = $bill->getAccountTotal();
            $base_imponible = $bill->getAccountTotal() / 1.18;
            $iva = $total - $base_imponible;
            $retencion = $iva * 0.278;

            $uArr = [
                'fecha_venta' => Carbon::now()->locale('es')->isoFormat('D [de] MMMM YYYY'),
                'nombreSRL' => $vendor->shipping_name,
                'dirigidoA' => $vendor->billing_name,
                'montoTotal' => \Auth::user()->priceFormat($total),
                'retencion' => \Auth::user()->priceFormat($retencion),
                'montoCompleto' => \Auth::user()->priceFormat($total - $retencion),
                'montoTotalTexto' => $this->dineroATexto($total - $retencion),
                'numero_factura' => \Auth::user()->billNumberFormat($bill->bill_id),
                'detalle' => $bill->items[0]->product['name'],
                'numero_orden' => $bill->order_number,
                'cuentas_afectadas' => $bill->accounts,
            ];
            $resp = Utility::sendEmailTemplateWithDocumentAuthorizationTransfer('bill_sent', env("MAIL_TO"), $uArr);
            return response()->json(['success' => true, 'msg' => $resp]);
        } catch (\Exception $e) {
            $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
            return response()->json(['success' => false]);
        }
    }

    public function payment($bill_id)
    {
        if (\Auth::user()->can('create payment bill')) {
            $bill    = Bill::where('id', $bill_id)->first();
            $venders = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            $categories = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $accounts   = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('bill.payment', compact('venders', 'categories', 'accounts', 'bill'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function createPayment(Request $request, $bill_id)
    {
        if (\Auth::user()->can('create payment bill')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'date' => 'required',
                    'amount' => 'required',
                    'account_id' => 'required',

                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $billPayment                 = new BillPayment();
            $billPayment->bill_id        = $bill_id;
            $billPayment->date           = $request->date;
            $billPayment->amount         = $request->amount;
            $billPayment->account_id     = $request->account_id;
            $billPayment->payment_method = 0;
            $billPayment->reference      = $request->reference;
            $billPayment->description    = $request->description;
            if (!empty($request->add_receipt)) {
                $fileName = time() . "_" . $request->add_receipt->getClientOriginalName();
                // $request->add_receipt->storeAs('uploads/payment', $fileName);
                $billPayment->add_receipt = $fileName;

                $dir        = 'uploads/payment';
                $path = Utility::upload_file($request, 'add_receipt', $fileName, $dir, []);
                // $request->add_receipt  = $path['url'];
                if ($path['flag'] == 1) {
                    $url = $path['url'];
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }

                $billPayment->save();
            }
            $billPayment->save();

            $bill  = Bill::where('id', $bill_id)->first();
            $due   = $bill->getDue();
            $total = $bill->getTotal();

            if ($bill->status == 0) {
                $bill->send_date = date('Y-m-d');
                $bill->save();
            }

            if ($due <= 0) {
                $bill->status = 4;
                $bill->save();
            } else {
                $bill->status = 3;
                $bill->save();
            }
            $billPayment->user_id    = $bill->vender_id;
            $billPayment->user_type  = 'Vender';
            $billPayment->type       = 'Partial';
            $billPayment->created_by = \Auth::user()->id;
            $billPayment->payment_id = $billPayment->id;
            $billPayment->category   = 'Bill';
            $billPayment->account    = $request->account_id;
            Transaction::addTransaction($billPayment);

            $vender = Vender::where('id', $bill->vender_id)->first();

            $payment         = new BillPayment();
            $payment->name   = $vender['name'];
            $payment->method = '-';
            $payment->date   = \Auth::user()->dateFormat($request->date);
            $payment->amount = \Auth::user()->priceFormat($request->amount);
            $payment->bill   = 'bill ' . \Auth::user()->billNumberFormat($billPayment->bill_id);

            Utility::updateUserBalance('vendor', $bill->vender_id, $request->amount, 'credit');

            Utility::bankAccountBalance($request->account_id, $request->amount, 'debit');

            $billPayments = BillPayment::where('bill_id', $bill->id)->get();
            foreach ($billPayments as $billPayment) {
                $accountId = BankAccount::find($billPayment->account_id);

                $data = [
                    'account_id' => $accountId->chart_account_id,
                    'transaction_type' => 'Debit',
                    'transaction_amount' => $billPayment->amount,
                    'reference' => 'Bill Payment',
                    'reference_id' => $bill->id,
                    'reference_sub_id' => $billPayment->id,
                    'date' => $billPayment->date,
                ];
                Utility::addTransactionLines($data);
            }

            $uArr = [
                'payment_name' => $payment->name,
                'payment_bill' => $payment->bill,
                'payment_amount' => $payment->amount,
                'payment_date' => $payment->date,
                'payment_method' => $payment->method

            ];
            try {
                $resp = Utility::sendEmailTemplate('new_bill_payment', [$vender->id => $vender->email], $uArr);
            } catch (\Exception $e) {
                $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
            }

            return redirect()->back()->with('success', __('Payment successfully added.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
        }
    }

    public function paymentDestroy(Request $request, $bill_id, $payment_id)
    {
        if (\Auth::user()->can('delete payment bill')) {
            $payment = BillPayment::find($payment_id);
            BillPayment::where('id', '=', $payment_id)->delete();

            $bill = Bill::where('id', $bill_id)->first();

            $due   = $bill->getDue();
            $total = $bill->getTotal();

            if ($due > 0 && $total != $due) {
                $bill->status = 3;
            } else {
                $bill->status = 2;
            }
            TransactionLines::where('reference_sub_id', $payment_id)->where('reference', 'Bill Payment')->delete();

            Utility::updateUserBalance('vendor', $bill->vender_id, $payment->amount, 'debit');

            Utility::bankAccountBalance($payment->account_id, $payment->amount, 'credit');


            $bill->save();
            $type = 'Partial';
            $user = 'Vender';
            Transaction::destroyTransaction($payment_id, $type, $user);

            return redirect()->back()->with('success', __('Payment successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function venderBill(Request $request)
    {
        if (\Auth::user()->can('manage vender bill')) {

            $status = Status::getAllAsArray();

            $query = Bill::where('vender_id', '=', \Auth::user()->vender_id)->where('status', '!=', '0')->where('created_by', \Auth::user()->creatorId());

            if (!empty($request->vender)) {
                $query->where('id', '=', $request->vender);
            }
            if (str_contains($request->bill_date, ' to ')) {
                $date_range = explode(' to ', $request->bill_date);
                $query->whereBetween('bill_date', $date_range);
            } elseif (!empty($request->bill_date)) {

                $query->where('bill_date', $request->bill_date);
            }

            // if (!empty($request->bill_date)) {
            //     $date_range = explode(' to ', $request->bill_date);
            //     $query->whereBetween('bill_date', $date_range);
            // }

            if (!empty($request->status)) {
                $query->where('status', '=', $request->status);
            }
            $bills = $query->get();

            return view('bill.index', compact('bills', 'status'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function venderBillShow($id)
    {
        if (\Auth::user()->can('show bill')) {
            $bill_id = Crypt::decrypt($id);
            $bill    = Bill::where('id', $bill_id)->first();

            if ($bill->created_by == \Auth::user()->creatorId()) {
                $vendor = $bill->vender;
                $items = $bill->items;
                $items     = [];
                return view('bill.view', compact('bill', 'vendor',  'items'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function vender(Request $request)
    {
        $vender = Vender::where('id', '=', $request->id)->first();

        return view('bill.vender_detail', compact('vender'));
    }

    public function estatus_bills($bill_id)
    {
        $bill  = Bill::where('id', $bill_id)->first();
        return response()->json(['status' => $bill->status, 'bill_id' => $bill->id, 'message' => __('Bill status successfully changed.')]);
    }


    public function venderBillSend($bill_id)
    {
        return view('vender.bill_send', compact('bill_id'));
    }



    public function venderBillSendMail(Request $request, $bill_id)
    {

        $validator = \Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $email = $request->email;
        $bill  = Bill::where('id', $bill_id)->first();

        $vender     = Vender::where('id', $bill->vender_id)->first();
        $bill->name = !empty($vender) ? $vender->name : '';
        $bill->bill = \Auth::user()->billNumberFormat($bill->bill_id);

        $billId    = Crypt::encrypt($bill->id);
        $bill->url = route('bill.pdf', $billId);

        $uArr = [
            'bill_name' => $bill->name,
            'bill_number' => $bill->bill,
            'bill_url' => $bill->url,
        ];
        try {
            $resp = Utility::sendEmailTemplate('vendor_bill_sent', [$vender->id => $vender->email], $uArr);
        } catch (\Exception $e) {
            $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
        }

        return redirect()->back()->with('success', __('Bill successfully sent.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
    }

    public function shippingDisplay(Request $request, $id)
    {
        $bill = Bill::find($id);

        if ($request->is_display == 'true') {
            $bill->shipping_display = 1;
        } else {
            $bill->shipping_display = 0;
        }
        $bill->save();

        return redirect()->back()->with('success', __('Shipping address status successfully changed.'));
    }

    public function duplicate($bill_id)
    {
        if (\Auth::user()->can('duplicate bill')) {
            $bill = Bill::where('id', $bill_id)->first();

            $duplicateBill                   = new Bill();
            $duplicateBill->bill_id          = $this->billNumber();
            $duplicateBill->vender_id        = $bill['vender_id'];
            $duplicateBill->bill_date        = date('Y-m-d');
            $duplicateBill->due_date         = $bill['due_date'];
            $duplicateBill->send_date        = null;
            $duplicateBill->category_id      = $bill['category_id'];
            $duplicateBill->order_number     = $bill['order_number'];
            $duplicateBill->status           = 0;
            $duplicateBill->shipping_display = $bill['shipping_display'];
            $duplicateBill->created_by       = $bill['created_by'];
            $duplicateBill->save();

            if ($duplicateBill) {
                $billProduct = BillProduct::where('bill_id', $bill_id)->get();
                foreach ($billProduct as $product) {
                    $duplicateProduct             = new BillProduct();
                    $duplicateProduct->bill_id    = $duplicateBill->id;
                    $duplicateProduct->product_id = $product->product_id;
                    $duplicateProduct->quantity   = $product->quantity;
                    $duplicateProduct->tax        = $product->tax;
                    $duplicateProduct->discount   = $product->discount;
                    $duplicateProduct->price      = $product->price;
                    $duplicateProduct->save();
                }
            }


            return redirect()->back()->with('success', __('Bill duplicate successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function previewBill($template, $color)
    {
        $objUser  = \Auth::user();
        $settings = Utility::settings();
        $bill     = new Bill();

        $vendor                   = new \stdClass();
        $vendor->email            = '<Email>';
        $vendor->shipping_name    = '<Vendor Name>';
        $vendor->shipping_country = '<Country>';
        $vendor->shipping_state   = '<State>';
        $vendor->shipping_city    = '<City>';
        $vendor->shipping_phone   = '<Vendor Phone Number>';
        $vendor->shipping_zip     = '<Zip>';
        $vendor->shipping_address = '<Address>';
        $vendor->billing_name     = '<Vendor Name>';
        $vendor->billing_country  = '<Country>';
        $vendor->billing_state    = '<State>';
        $vendor->billing_city     = '<City>';
        $vendor->billing_phone    = '<Vendor Phone Number>';
        $vendor->billing_zip      = '<Zip>';
        $vendor->billing_address  = '<Address>';
        $vendor->sku         = 'Test123';

        $totalTaxPrice = 0;
        $taxesData     = [];
        $items         = [];
        for ($i = 1; $i <= 3; $i++) {
            $item           = new \stdClass();
            $item->name     = 'Item ' . $i;
            $item->quantity = 1;
            $item->tax      = 5;
            $item->discount = 50;
            $item->price    = 100;

            $taxes = [
                'Tax 1',
                'Tax 2',
            ];

            $itemTaxes = [];
            foreach ($taxes as $k => $tax) {
                $taxPrice         = 10;
                $totalTaxPrice    += $taxPrice;
                $itemTax['name']  = 'Tax ' . $k;
                $itemTax['rate']  = '10 %';
                $itemTax['price'] = '$10';
                $itemTax['tax_price'] = 10;
                $itemTaxes[]      = $itemTax;

                // $taxPrice         = 10;
                // $totalTaxPrice    += $taxPrice;
                // $itemTax['name']  = 'Tax ' . $k;
                // $itemTax['rate']  = '10 %';
                // $itemTax['price'] = '$10';
                // $itemTaxes[]      = $itemTax;
                if (array_key_exists('Tax ' . $k, $taxesData)) {
                    $taxesData['Tax ' . $k] = $taxesData['Tax 1'] + $taxPrice;
                } else {
                    $taxesData['Tax ' . $k] = $taxPrice;
                }
            }
            $item->itemTax = $itemTaxes;
            $items[]       = $item;
        }

        $bill->bill_id    = 1;
        $bill->issue_date = date('Y-m-d H:i:s');
        $bill->due_date   = date('Y-m-d H:i:s');
        $bill->itemData   = $items;

        $bill->totalTaxPrice = 60;
        $bill->totalQuantity = 3;
        $bill->totalRate     = 300;
        $bill->totalDiscount = 10;
        $bill->taxesData     = $taxesData;
        $bill->customField   = [];
        $customFields        = [];

        $preview      = 1;
        $color        = '#' . $color;
        $font_color   = Utility::getFontColor($color);
        $logo         = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName('company_logo_dark');
        $bill_logo = Utility::getValByName('bill_logo');
        if (isset($bill_logo) && !empty($bill_logo)) {

            $img = Utility::get_file($bill_logo);

            $img = Utility::get_file('bill_logo/') . $bill_logo;


            // $img = asset(\Storage::url('bill_logo/') . $bill_logo);
        } else {
            $img          = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));
        }


        return view('bill.templates.' . $template, compact('bill', 'preview', 'color', 'img', 'settings', 'vendor', 'font_color', 'customFields'));
    }

    public function bill($bill_id)
    {
        $settings = Utility::settings();
        $billId   = Crypt::decrypt($bill_id);

        $bill  = Bill::where('id', $billId)->first();
        $data  = DB::table('settings');
        $data  = $data->where('created_by', '=', $bill->created_by);
        $data1 = $data->get();

        foreach ($data1 as $row) {
            $settings[$row->name] = $row->value;
        }

        $vendor = $bill->vender;

        $totalTaxPrice = 0;
        $totalQuantity = 0;
        $totalRate     = 0;
        $totalDiscount = 0;
        $taxesData     = [];
        $items         = [];

        foreach ($bill->items as $product) {

            $item              = new \stdClass();
            $item->name        = !empty($product->product) ? $product->product->name : '';
            $item->quantity    = $product->quantity;
            $item->tax         = $product->tax;
            $item->discount    = $product->discount;
            $item->price       = $product->price;
            $item->description = $product->description;

            $totalQuantity += $item->quantity;
            $totalRate     += $item->price;
            $totalDiscount += $item->discount;

            $taxes     = Utility::tax($product->tax);
            $itemTaxes = [];
            if (!empty($item->tax)) {
                foreach ($taxes as $tax) {
                    $taxPrice      = Utility::taxRate($tax->rate, $item->price, $item->quantity, $item->discount);
                    $totalTaxPrice += $taxPrice;

                    $itemTax['name']  = $tax->name;
                    $itemTax['rate']  = $tax->rate . '%';
                    $itemTax['price'] = Utility::priceFormat($settings, $taxPrice);
                    $itemTax['tax_price'] = $taxPrice;
                    $itemTaxes[]      = $itemTax;


                    if (array_key_exists($tax->name, $taxesData)) {
                        $taxesData[$tax->name] = $taxesData[$tax->name] + $taxPrice;
                    } else {
                        $taxesData[$tax->name] = $taxPrice;
                    }
                }

                $item->itemTax = $itemTaxes;
            } else {
                $item->itemTax = [];
            }
            $items[] = $item;
        }

        $bill->itemData      = $items;
        $bill->totalTaxPrice = $totalTaxPrice;
        $bill->totalQuantity = $totalQuantity;
        $bill->totalRate     = $totalRate;
        $bill->totalDiscount = $totalDiscount;
        $bill->taxesData     = $taxesData;
        $bill->customField   = CustomField::getData($bill, 'bill');
        $customFields        = [];
        if (!empty(\Auth::user())) {
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'bill')->get();
        }

        //Set your logo
        $logo         = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName('company_logo_dark');
        $settings_data = \App\Models\Utility::settingsById($bill->created_by);
        $bill_logo = $settings_data['bill_logo'];
        if (isset($bill_logo) && !empty($bill_logo)) {
            // $img = Utility::get_file('bill_logo') . $bill_logo;
            $img          = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));

            // $img = asset(\Storage::url('bill_logo/') . $bill_logo);
            // $img = Utility::get_file($bill_logo);
        } else {
            $img          = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));
        }


        if ($bill) {
            $color      = '#' . $settings['bill_color'];
            $font_color = Utility::getFontColor($color);

            return view('bill.templates.' . $settings['bill_template'], compact('bill', 'color', 'settings', 'vendor', 'img', 'font_color', 'customFields'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function saveBillTemplateSettings(Request $request)
    {
        $user = \Auth::user();
        $post = $request->all();
        unset($post['_token']);

        if ($request->bill_logo) {
            $request->validate(
                [
                    'bill_logo' => 'image',
                ]
            );



            $dir = 'bill_logo/';
            $bill_logo         = $user->id . '_bill_logo.png';
            $validation = [
                'mimes:' . 'png',
                'max:' . '20480',
            ];

            $path = Utility::upload_file($request, 'bill_logo', $bill_logo, $dir, $validation);
            if ($path['flag'] == 1) {
                $retainer_logo = $path['url'];
            } else {
                return redirect()->back()->with('error', __($path['msg']));
            }



            // $path                 = $request->file('bill_logo')->storeAs('/bill_logo', $bill_logo);
            $post['bill_logo'] = $bill_logo;
        }

        if (isset($post['bill_template']) && (!isset($post['bill_color']) || empty($post['bill_color']))) {
            $post['bill_color'] = "ffffff";
        }

        foreach ($post as $key => $data) {
            \DB::insert(
                'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $data,
                    $key,
                    \Auth::user()->creatorId(),
                ]
            );
        }

        return redirect()->back()->with('success', __('Bill Setting updated successfully'));
    }

    public function items(Request $request)
    {
        $items = BillProduct::where('bill_id', $request->bill_id)->where('product_id', $request->product_id)->first();

        return json_encode($items);
    }

    public function paybill($bill_id)
    {

        if (!empty($bill_id)) {
            try {
                $id = \Illuminate\Support\Facades\Crypt::decrypt($bill_id);
                $bill = bill::where('id', $id)->first();
                if (!is_null($bill)) {

                    $settings = Utility::settings();

                    $items         = [];
                    $totalTaxPrice = 0;
                    $totalQuantity = 0;
                    $totalRate     = 0;
                    $totalDiscount = 0;
                    $taxesData     = [];

                    foreach ($bill->items as $item) {
                        $totalQuantity += $item->quantity;
                        $totalRate     += $item->price;
                        $totalDiscount += $item->discount;
                        $taxes         = Utility::tax($item->tax);

                        $itemTaxes = [];
                        foreach ($taxes as $tax) {
                            if (!empty($tax)) {
                                $taxPrice            = Utility::taxRate($tax->rate, $item->price, $item->quantity);
                                $totalTaxPrice       += $taxPrice;
                                $itemTax['tax_name'] = $tax->tax_name;
                                $itemTax['tax']      = $tax->tax . '%';
                                $itemTax['price']    = Utility::priceFormat($settings, $taxPrice);
                                $itemTaxes[]         = $itemTax;

                                if (array_key_exists($tax->name, $taxesData)) {
                                    $taxesData[$itemTax['tax_name']] = $taxesData[$tax->tax_name] + $taxPrice;
                                } else {
                                    $taxesData[$tax->tax_name] = $taxPrice;
                                }
                            } else {
                                $taxPrice            = Utility::taxRate(0, $item->price, $item->quantity);
                                $totalTaxPrice       += $taxPrice;
                                $itemTax['tax_name'] = 'No Tax';
                                $itemTax['tax']      = '';
                                $itemTax['price']    = Utility::priceFormat($settings, $taxPrice);
                                $itemTaxes[]         = $itemTax;

                                if (array_key_exists('No Tax', $taxesData)) {
                                    $taxesData[$tax->tax_name] = $taxesData['No Tax'] + $taxPrice;
                                } else {
                                    $taxesData['No Tax'] = $taxPrice;
                                }
                            }
                        }
                        $item->itemTax = $itemTaxes;
                        $items[]       = $item;
                    }
                    $bill->items         = $items;
                    $bill->totalTaxPrice = $totalTaxPrice;
                    $bill->totalQuantity = $totalQuantity;
                    $bill->totalRate     = $totalRate;
                    $bill->totalDiscount = $totalDiscount;
                    $bill->taxesData     = $taxesData;
                    $ownerId = $bill->created_by;
                    $company_setting = Utility::settingById($ownerId);
                    $payment_setting = Utility::bill_payment_settings($ownerId);

                    $users = User::where('id', $bill->created_by)->first();

                    if (!is_null($users)) {
                        \App::setLocale($users->lang);
                    } else {
                        $users = User::where('type', 'owner')->first();
                        \App::setLocale($users->lang);
                    }


                    $bill    = bill::where('id', $id)->first();
                    $customer = $bill->customer;
                    $iteams   = $bill->items;
                    $company_payment_setting = Utility::getCompanyPaymentSetting($bill->created_by);

                    return view('bill.billpay', compact('bill', 'iteams', 'company_setting', 'users', 'payment_setting'));
                } else {
                    return abort('404', 'The Link You Followed Has Expired');
                }
            } catch (Exception $e) {
                return redirect()->back()->with('error', __('Not found.'));
            }
        } else {
            return abort('404', 'The Link You Followed Has Expired');
        }
    }

    public function pdffrombill($id)
    {
        $settings = Utility::settings();

        $billId = Crypt::decrypt($id);
        $bill   = bill::where('id', $billId)->first();

        $data  = \DB::table('settings');
        $data  = $data->where('created_by', '=', $bill->created_by);
        $data1 = $data->get();

        foreach ($data1 as $row) {
            $settings[$row->name] = $row->value;
        }

        $user         = new User();
        $user->name   = $bill->name;
        $user->email  = $bill->contacts;
        $user->mobile = $bill->contacts;

        $user->bill_address = $bill->billing_address;
        $user->bill_zip     = $bill->billing_postalcode;
        $user->bill_city    = $bill->billing_city;
        $user->bill_country = $bill->billing_country;
        $user->bill_state   = $bill->billing_state;

        $user->address = $bill->shipping_address;
        $user->zip     = $bill->shipping_postalcode;
        $user->city    = $bill->shipping_city;
        $user->country = $bill->shipping_country;
        $user->state   = $bill->shipping_state;


        $items         = [];
        $totalTaxPrice = 0;
        $totalQuantity = 0;
        $totalRate     = 0;
        $totalDiscount = 0;
        $taxesData     = [];

        foreach ($bill->items as $product) {
            $item           = new \stdClass();
            $item->name     = $product->item;
            $item->quantity = $product->quantity;
            $item->tax      = !empty($product->taxs) ? $product->taxs->rate : '';
            $item->discount = $product->discount;
            $item->price    = $product->price;

            $totalQuantity += $item->quantity;
            $totalRate     += $item->price;
            $totalDiscount += $item->discount;

            $taxes     = \Utility::tax($product->tax);
            $itemTaxes = [];
            foreach ($taxes as $tax) {
                $taxPrice      = \Utility::taxRate($tax->rate, $item->price, $item->quantity);
                $totalTaxPrice += $taxPrice;

                $itemTax['name']  = $tax->tax_name;
                $itemTax['rate']  = $tax->rate . '%';
                $itemTax['price'] = \App\Models\Utility::priceFormat($settings, $taxPrice);
                $itemTaxes[]      = $itemTax;


                if (array_key_exists($tax->tax_name, $taxesData)) {
                    $taxesData[$tax->tax_name] = $taxesData[$tax->tax_name] + $taxPrice;
                } else {
                    $taxesData[$tax->tax_name] = $taxPrice;
                }
            }
            $item->itemTax = $itemTaxes;
            $items[]       = $item;
        }

        $bill->items         = $items;
        $bill->totalTaxPrice = $totalTaxPrice;
        $bill->totalQuantity = $totalQuantity;
        $bill->totalRate     = $totalRate;
        $bill->totalDiscount = $totalDiscount;
        $bill->taxesData     = $taxesData;

        //Set your logo
        $logo         = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName('company_logo_dark');
        $settings_data = \App\Models\Utility::settingsById($bill->created_by);
        $bill_logo = $settings_data['bill_logo'];
        if (isset($bill_logo) && !empty($bill_logo)) {
            $img = asset(\Storage::url('bill_logo/') . $bill_logo);
        } else {
            $img          = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));
        }

        if ($bill) {
            $color      = '#' . $settings['bill_color'];
            $font_color = Utility::getFontColor($color);

            return view('bill.templates.' . $settings['bill_template'], compact('bill', 'user', 'color', 'settings', 'img', 'font_color'));
        } else {
            return redirect()->route('pay.billpay', \Illuminate\Support\Facades\Crypt::encrypt($billId))->with('error', __('Permission denied.'));
        }
    }

    public function export()
    {
        $name = 'bill_' . date('Y-m-d i:h:s');
        $data = Excel::download(new BillExport(), $name . '.xlsx');

        return $data;
    }
}
