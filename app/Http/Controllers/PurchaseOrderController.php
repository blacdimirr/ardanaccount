<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Bill;
use App\Models\BillProduct;
use App\Models\Vender;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function byVendorAndOrder(Request $request)
    {
        $data = $request->validate([
            'vendor_id'    => ['required', 'integer'],      // ID del proveedor (tabla venders)
            'order_number' => ['required', 'string'],       // Número de orden/compra (campo en bills)
        ]);

        $bill = Bill::with(['items.product.unit:id,name', 'items.product:id,name,description,sku,unit_id'])
            ->where('vender_id', $data['vendor_id'])
            ->where('order_number', $data['order_number'])
            ->first();


        if (!$bill) {
            return response()->json([
                'message' => 'No se encontró la orden para ese proveedor y número.',
            ], 404);
        }

        $products = $bill->items->map(function ($item) {
            $desc = $item->product?->description ?? $item->product?->name ?? 'Producto';
            return [
                'description'        => $desc,
                'quantity'           => (float)($item->quantity ?? 0),
                'received_quantity'  => 0,   // siempre 0
                'sku'               => $item->product->sku ?? 'SKU no disponible',
                'name'              => $item->product->name ?? 'Nombre no disponible',
                'unit'             => $item->product->unit?->name ?? 'Unidad no disponible',
            ];
        })->values();

        return response()->json([
            'supplier'     => ['id' => $bill->vender_id, 'name' => $bill->vender?->name ?? 'Proveedor'],
            'order_number' => $bill->order_number,
            'products'     => $products,
        ]);
    }

    public function updateByVendorAndOrder(Request $request)
    {
        $data = $request->validate([
            'vendor_id'        => ['required', 'integer'],
            'order_number'     => ['required', 'string'],
            'product_sku'      => ['required', 'string'],
            'received_quantity' => ['required', 'numeric', 'min:0'], // 👈 lo que quieres actualizar
        ]);

        // Busca la orden
        $bill = Bill::query()
            ->where('vender_id', $data['vendor_id'])
            ->where(function ($q) use ($data) {
                $q->where('order_number', $data['order_number']);
            })
            ->first();

        if (!$bill) {
            return response()->json(['message' => 'No se encontró la orden para ese proveedor y número.'], 404);
        }

        // Trae las líneas con su producto (necesitamos el SKU)
        $items = $bill->items()
            ->with(['product:id,sku,name,description'])
            ->get();

        // Filtra por SKU
        $linesToUpdate = $items->filter(function ($line) use ($data) {
            return isset($line->product) && $line->product->sku === $data['product_sku'];
        });

        if ($linesToUpdate->isEmpty()) {
            return response()->json([
                'message' => 'El SKU no pertenece a esta orden.',
                'sku'     => $data['product_sku'],
            ], 404);
        }

        $updated = [];

        DB::transaction(function () use ($linesToUpdate, $data, &$updated) {
            foreach ($linesToUpdate as $line) {
                // (opcional) Regla: no permitir más de lo ordenado
                if ($data['received_quantity'] > (float)$line->quantity) {
                    abort(response()->json([
                        'message' => "La cantidad recibida ({$data['received_quantity']}) supera la cantidad ordenada ({$line->quantity}) para el SKU {$line->product->sku}.",
                        'code'    => 'RECEIVE_GT_ORDERED',
                    ], 422));
                }

                // ✅ Actualiza la LÍNEA de compra, no el producto maestro
                $line->received_quantity = (int)$data['received_quantity']; // o (float) si lo definiste decimal
                $line->save();

                $updated[] = [
                    'bill_product_id'   => $line->id,
                    'product_id'        => $line->product_id,
                    'sku'               => $line->product->sku,
                    'description'       => $line->product->description ?? $line->product->name ?? 'Producto',
                    'quantity'          => (float)$line->quantity,
                    'received_quantity' => (float)$line->received_quantity,
                ];
            }
        });

        return response()->json([
            'message'      => 'Cantidad recibida actualizada correctamente.',
            'order_number' => $bill->order_number,
            'updated'      => $updated,
        ]);
    }

    public function getSuppliers(Request $request)
    {
        $vender = Vender::all();

        if (!$vender) {
            return response()->json([
                'message' => 'No se encontró suplidores.',
            ], 404);
        }

        return response()->json([
            'supplier'     => $vender,
            'message' => "Suplidores encontrados correctamente.",
        ]);
    }
}
