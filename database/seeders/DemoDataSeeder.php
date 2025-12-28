<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\BillProduct;
use App\Models\Budget;
use App\Models\ChartOfAccount;
use App\Models\Contract;
use App\Models\ContractType;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\DebitNote;
use App\Models\Expense;
use App\Models\Goal;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\InvoiceProduct;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Payment;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\ProductServiceUnit;
use App\Models\Proposal;
use App\Models\ProposalProduct;
use App\Models\Retainer;
use App\Models\RetainerPayment;
use App\Models\RetainerProduct;
use App\Models\Revenue;
use App\Models\SupplierType;
use App\Models\Tax;
use App\Models\Transaction;
use App\Models\TransactionLines;
use App\Models\User;
use App\Models\Vender;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $company = User::where('type', 'company')->first();

        if (! $company) {
            return;
        }

        $faker = Faker::create('es_ES');

        $chartAccount = ChartOfAccount::firstOrCreate(
            [
                'name' => 'Cuenta Caja Demo',
                'created_by' => $company->id,
            ],
            [
                'code' => 1100,
                'type' => 1,
                'sub_type' => 1,
                'is_enabled' => 1,
                'description' => 'Cuenta de caja para datos de demostración.',
            ]
        );

        $bankAccount = BankAccount::firstOrCreate(
            [
                'holder_name' => 'Caja Demo',
                'created_by' => $company->id,
            ],
            [
                'bank_name' => 'Banco Demo',
                'account_number' => '0001234567',
                'chart_account_id' => $chartAccount->id,
                'opening_balance' => 25000,
                'contact_number' => $faker->phoneNumber,
                'bank_address' => $faker->address,
            ]
        );

        $tax = Tax::firstOrCreate(
            [
                'name' => 'ITBIS 18%',
                'created_by' => $company->id,
            ],
            [
                'rate' => '18',
            ]
        );

        $productCategory = ProductServiceCategory::firstOrCreate(
            [
                'name' => 'Productos Demo',
                'created_by' => $company->id,
            ],
            [
                'type' => 'product',
                'color' => '#4f46e5',
            ]
        );

        $serviceCategory = ProductServiceCategory::firstOrCreate(
            [
                'name' => 'Servicios Demo',
                'created_by' => $company->id,
            ],
            [
                'type' => 'service',
                'color' => '#16a34a',
            ]
        );

        $unit = ProductServiceUnit::firstOrCreate(
            [
                'name' => 'Unidad',
                'created_by' => $company->id,
            ]
        );

        $products = collect([
            [
                'name' => 'Servicio de consultoría',
                'sku' => 'SERV-001',
                'sale_price' => 4500,
                'purchase_price' => 2500,
                'quantity' => 25,
                'tax_id' => $tax->id,
                'category_id' => $serviceCategory->id,
                'unit_id' => $unit->id,
                'type' => 'service',
                'description' => 'Horas de consultoría para la demo.',
            ],
            [
                'name' => 'Licencia de software',
                'sku' => 'PROD-001',
                'sale_price' => 8500,
                'purchase_price' => 6000,
                'quantity' => 40,
                'tax_id' => $tax->id,
                'category_id' => $productCategory->id,
                'unit_id' => $unit->id,
                'type' => 'product',
                'description' => 'Suscripción anual simulada.',
            ],
            [
                'name' => 'Soporte técnico',
                'sku' => 'SERV-002',
                'sale_price' => 3200,
                'purchase_price' => 1800,
                'quantity' => 30,
                'tax_id' => $tax->id,
                'category_id' => $serviceCategory->id,
                'unit_id' => $unit->id,
                'type' => 'service',
                'description' => 'Paquete de soporte mensual.',
            ],
        ])->map(function (array $data) use ($company) {
            return ProductService::firstOrCreate(
                [
                    'sku' => $data['sku'],
                    'created_by' => $company->id,
                ],
                array_merge($data, ['created_by' => $company->id])
            );
        });

        $nextCustomerId = (int) Customer::where('created_by', $company->id)->max('customer_id');
        $customer = Customer::updateOrCreate(
            [
                'email' => 'cliente.demo@example.com',
                'created_by' => $company->id,
            ],
            [
                'customer_id' => $nextCustomerId ? $nextCustomerId + 1 : 1,
                'name' => 'Cliente Demo',
                'tax_number' => 'RNC-001',
                'password' => Hash::make('demo1234'),
                'contact' => $faker->phoneNumber,
                'billing_name' => 'Cliente Demo',
                'billing_country' => 'DO',
                'billing_state' => 'Distrito Nacional',
                'billing_city' => 'Santo Domingo',
                'billing_phone' => $faker->phoneNumber,
                'billing_zip' => '10101',
                'billing_address' => $faker->address,
                'shipping_name' => 'Cliente Demo',
                'shipping_country' => 'DO',
                'shipping_state' => 'Distrito Nacional',
                'shipping_city' => 'Santo Domingo',
                'shipping_phone' => $faker->phoneNumber,
                'shipping_zip' => '10101',
                'shipping_address' => $faker->address,
                'lang' => 'es',
                'balance' => 0,
            ]
        );

        $supplierType = SupplierType::firstOrCreate(
            [
                'name' => 'Servicios Profesionales',
                'created_by' => $company->id,
            ]
        );

        $nextVenderId = (int) Vender::where('created_by', $company->id)->max('vender_id');
        $vender = Vender::updateOrCreate(
            [
                'email' => 'proveedor.demo@example.com',
                'created_by' => $company->id,
            ],
            [
                'vender_id' => $nextVenderId ? $nextVenderId + 1 : 1,
                'name' => 'Proveedor Demo',
                'tax_number' => 'RNC-002',
                'supplier_type' => $supplierType->name,
                'password' => Hash::make('demo1234'),
                'contact' => $faker->phoneNumber,
                'billing_name' => 'Proveedor Demo',
                'billing_country' => 'DO',
                'billing_state' => 'Distrito Nacional',
                'billing_city' => 'Santo Domingo',
                'billing_phone' => $faker->phoneNumber,
                'billing_zip' => '10101',
                'billing_address' => $faker->address,
                'shipping_name' => 'Proveedor Demo',
                'shipping_country' => 'DO',
                'shipping_state' => 'Distrito Nacional',
                'shipping_city' => 'Santo Domingo',
                'shipping_phone' => $faker->phoneNumber,
                'shipping_zip' => '10101',
                'shipping_address' => $faker->address,
                'lang' => 'es',
                'balance' => 0,
            ]
        );

        $nextInvoiceId = (int) Invoice::where('created_by', $company->id)->max('invoice_id');
        $invoice = Invoice::create([
            'invoice_id' => $nextInvoiceId ? $nextInvoiceId + 1 : 1,
            'customer_id' => $customer->id,
            'issue_date' => now()->subDays(5)->toDateString(),
            'due_date' => now()->addDays(25)->toDateString(),
            'send_date' => now()->subDays(4)->toDateString(),
            'category_id' => $productCategory->id,
            'ref_number' => 'INV-DEMO-001',
            'status' => 1,
            'shipping_display' => 1,
            'discount_apply' => 0,
            'created_by' => $company->id,
        ]);

        $invoiceProduct = InvoiceProduct::create([
            'invoice_id' => $invoice->id,
            'product_id' => $products->first()->id,
            'quantity' => 2,
            'tax' => 18,
            'discount' => 0,
            'price' => 4500,
            'description' => 'Servicio principal de la demo.',
        ]);

        InvoicePayment::create([
            'invoice_id' => $invoice->id,
            'date' => now()->subDays(2)->toDateString(),
            'amount' => 9000,
            'account_id' => $bankAccount->id,
            'payment_method' => 1,
            'reference' => 'PAY-INV-001',
            'description' => 'Pago inicial de la factura demo.',
        ]);

        $bill = Bill::create([
            'bill_id' => 'BILL-DEMO-001',
            'vender_id' => $vender->id,
            'bill_date' => now()->subDays(7)->toDateString(),
            'due_date' => now()->addDays(20)->toDateString(),
            'order_number' => 1001,
            'status' => 1,
            'shipping_display' => 1,
            'send_date' => now()->subDays(6)->toDateString(),
            'discount_apply' => 0,
            'category_id' => $productCategory->id,
            'created_by' => $company->id,
        ]);

        $billProduct = BillProduct::create([
            'bill_id' => $bill->id,
            'product_id' => $products->get(1)->id,
            'quantity' => 1,
            'tax' => 18,
            'discount' => 0,
            'price' => 8500,
            'description' => 'Compra simulada para demo.',
        ]);

        BillPayment::create([
            'bill_id' => $bill->id,
            'date' => now()->subDays(1)->toDateString(),
            'amount' => 8500,
            'account_id' => $bankAccount->id,
            'payment_method' => 1,
            'reference' => 'PAY-BILL-001',
            'description' => 'Pago de la factura proveedor demo.',
        ]);

        Expense::create([
            'category_id' => $productCategory->id,
            'description' => 'Gasto operativo de prueba.',
            'amount' => 1800,
            'date' => now()->subDays(3)->toDateString(),
            'project' => 0,
            'user_id' => $company->id,
            'created_by' => $company->id,
        ]);

        Revenue::create([
            'date' => now()->subDays(2)->toDateString(),
            'amount' => 9200,
            'account_id' => $bankAccount->id,
            'customer_id' => $customer->id,
            'category_id' => $serviceCategory->id,
            'payment_method' => 1,
            'reference' => 'REV-001',
            'description' => 'Ingreso por servicios demo.',
            'created_by' => $company->id,
        ]);

        $payment = Payment::create([
            'date' => now()->subDays(1)->toDateString(),
            'amount' => 3000,
            'account_id' => $bankAccount->id,
            'vender_id' => $vender->id,
            'description' => 'Pago programado a proveedor.',
            'category_id' => $productCategory->id,
            'payment_method' => 1,
            'reference' => 'PAY-001',
            'created_by' => $company->id,
        ]);

        Transaction::create([
            'user_id' => $customer->id,
            'user_type' => 'customer',
            'account' => $chartAccount->id,
            'type' => 'credit',
            'amount' => 9000,
            'description' => 'Transacción de ingreso demo.',
            'date' => now()->subDays(2)->toDateString(),
            'created_by' => $company->id,
            'payment_id' => $payment->id,
            'category' => 'income',
        ]);

        TransactionLines::create([
            'account_id' => $chartAccount->id,
            'reference' => 'invoice',
            'reference_id' => $invoice->id,
            'reference_sub_id' => $invoiceProduct->id,
            'date' => now()->subDays(2)->toDateString(),
            'credit' => 9000,
            'debit' => 0,
            'created_by' => $company->id,
        ]);

        $journalEntry = JournalEntry::create([
            'date' => now()->subDay()->toDateString(),
            'reference' => 'JRN-001',
            'description' => 'Asiento contable demo.',
            'created_by' => $company->id,
        ]);

        JournalItem::create([
            'journal' => $journalEntry->id,
            'account' => $chartAccount->id,
            'description' => 'Detalle asiento demo.',
            'debit' => 9000,
            'credit' => 0,
        ]);

        Budget::create([
            'name' => 'Presupuesto Demo',
            'period' => '2024',
            'from' => now()->startOfYear()->toDateString(),
            'to' => now()->endOfYear()->toDateString(),
            'income_data' => json_encode(['sales' => 150000]),
            'expense_data' => json_encode(['operations' => 75000]),
            'created_by' => $company->id,
        ]);

        Goal::create([
            'name' => 'Meta de ventas demo',
            'type' => 'invoice',
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->endOfMonth()->toDateString(),
            'amount' => 25000,
            'is_display' => 1,
            'created_by' => $company->id,
        ]);

        Asset::create([
            'name' => 'Laptop Demo',
            'purchase_date' => now()->subMonths(2)->toDateString(),
            'supported_date' => now()->addYear()->toDateString(),
            'amount' => 1500,
            'description' => 'Activo fijo para pruebas.',
            'created_by' => $company->id,
        ]);

        $contractType = ContractType::firstOrCreate(
            [
                'name' => 'Servicios',
                'created_by' => $company->id,
            ]
        );

        Contract::create([
            'customer' => $customer->id,
            'subject' => 'Contrato de servicios demo',
            'value' => 12000,
            'type' => $contractType->id,
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'edit_status' => 'active',
            'description' => 'Contrato generado para demo.',
            'notes' => 'Notas internas de prueba.',
            'created_by' => $company->id,
        ]);

        $nextProposalId = (int) Proposal::where('created_by', $company->id)->max('proposal_id');
        $proposal = Proposal::create([
            'proposal_id' => $nextProposalId ? $nextProposalId + 1 : 1,
            'customer_id' => $customer->id,
            'issue_date' => now()->subDays(3)->toDateString(),
            'send_date' => now()->subDays(2)->toDateString(),
            'category_id' => $serviceCategory->id,
            'status' => 1,
            'discount_apply' => 0,
            'is_convert' => 0,
            'converted_invoice_id' => 0,
            'converted_retainer_id' => 0,
            'created_by' => $company->id,
        ]);

        ProposalProduct::create([
            'proposal_id' => $proposal->id,
            'product_id' => $products->first()->id,
            'quantity' => 1,
            'tax' => 18,
            'discount' => 0,
            'price' => 4500,
            'description' => 'Producto propuesto para demo.',
        ]);

        $nextRetainerId = (int) Retainer::where('created_by', $company->id)->max('retainer_id');
        $retainer = Retainer::create([
            'retainer_id' => $nextRetainerId ? $nextRetainerId + 1 : 1,
            'customer_id' => $customer->id,
            'issue_date' => now()->subDays(4)->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'send_date' => now()->subDays(3)->toDateString(),
            'category_id' => $serviceCategory->id,
            'status' => 1,
            'discount_apply' => 0,
            'converted_invoice_id' => 0,
            'is_convert' => 0,
            'created_by' => $company->id,
        ]);

        RetainerProduct::create([
            'retainer_id' => $retainer->id,
            'product_id' => $products->get(2)->id,
            'quantity' => 1,
            'tax' => 18,
            'discount' => 0,
            'price' => 3200,
            'description' => 'Retainer de soporte demo.',
        ]);

        RetainerPayment::create([
            'retainer_id' => $retainer->id,
            'date' => now()->subDays(1)->toDateString(),
            'amount' => 3200,
            'account_id' => $bankAccount->id,
            'payment_method' => 1,
            'reference' => 'PAY-RET-001',
            'description' => 'Pago del retainer demo.',
        ]);

        CreditNote::create([
            'invoice' => $invoice->id,
            'customer' => $customer->id,
            'amount' => 500,
            'date' => now()->subDay()->toDateString(),
            'description' => 'Nota de crédito demo.',
        ]);

        DebitNote::create([
            'bill' => $bill->id,
            'vendor' => $vender->id,
            'amount' => 350,
            'date' => now()->subDay()->toDateString(),
            'description' => 'Nota de débito demo.',
        ]);
    }
}
