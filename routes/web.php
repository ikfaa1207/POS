<?php

use App\Http\Controllers\Clients\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Invoices\InvoiceController;
use App\Http\Controllers\Invoices\InvoiceFinalizeController;
use App\Http\Controllers\Invoices\InvoiceItemController;
use App\Http\Controllers\Invoices\InvoiceVoidController;
use App\Http\Controllers\Inventory\InventoryAdjustmentController;
use App\Http\Controllers\Invites\InviteController;
use App\Http\Controllers\Payments\InvoicePaymentController;
use App\Http\Controllers\Payments\PaymentReversalController;
use App\Http\Controllers\Permissions\RolePermissionController;
use App\Http\Controllers\Products\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Reports\ReportsController;
use App\Http\Controllers\Users\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'permission:dashboard.view'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/clients', [ClientController::class, 'index'])
        ->middleware('permission:client.view')
        ->name('clients.index');
    Route::get('/clients/create', [ClientController::class, 'create'])
        ->middleware('permission:client.create')
        ->name('clients.create');
    Route::post('/clients', [ClientController::class, 'store'])
        ->middleware('permission:client.create')
        ->name('clients.store');
    Route::get('/clients/{client}', [ClientController::class, 'show'])
        ->middleware('permission:client.view')
        ->name('clients.show');
    Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])
        ->middleware('permission:client.update')
        ->name('clients.edit');
    Route::patch('/clients/{client}', [ClientController::class, 'update'])
        ->middleware('permission:client.update')
        ->name('clients.update');

    Route::get('/invoices', [InvoiceController::class, 'index'])
        ->middleware('permission:invoice.view')
        ->name('invoices.index');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])
        ->middleware('permission:invoice.create')
        ->name('invoices.create');
    Route::post('/invoices', [InvoiceController::class, 'store'])
        ->middleware('permission:invoice.create')
        ->name('invoices.store');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])
        ->middleware('permission:invoice.view')
        ->name('invoices.show');
    Route::post('/invoices/{invoice}/items', [InvoiceItemController::class, 'store'])
        ->middleware('permission:invoice.edit')
        ->name('invoices.items.store');
    Route::delete('/invoices/{invoice}/items/{item}', [InvoiceItemController::class, 'destroy'])
        ->middleware('permission:invoice.edit')
        ->name('invoices.items.destroy');
    Route::post('/invoices/{invoice}/finalize', InvoiceFinalizeController::class)
        ->middleware('permission:invoice.finalize')
        ->name('invoices.finalize');
    Route::post('/invoices/{invoice}/void', InvoiceVoidController::class)
        ->middleware('permission:invoice.void')
        ->name('invoices.void');
    Route::post('/invoices/{invoice}/payments', [InvoicePaymentController::class, 'store'])
        ->middleware('permission:payment.record')
        ->name('invoices.payments.store');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/products', [ProductController::class, 'index'])
        ->middleware('permission:product.view')
        ->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])
        ->middleware('permission:product.create')
        ->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])
        ->middleware('permission:product.create')
        ->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])
        ->middleware('permission:product.update')
        ->name('products.edit');
    Route::patch('/products/{product}', [ProductController::class, 'update'])
        ->middleware('permission:product.update')
        ->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])
        ->middleware('permission:product.delete')
        ->name('products.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/payments/{payment}/reverse', PaymentReversalController::class)
        ->middleware('permission:payment.reverse')
        ->name('payments.reverse');
    Route::post('/products/{product}/inventory-adjustments', [InventoryAdjustmentController::class, 'store'])
        ->middleware('permission:inventory.adjust')
        ->name('products.inventory.adjust');
    Route::get('/reports', [ReportsController::class, 'index'])
        ->middleware('permission:reports.view')
        ->name('reports.index');
    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:user.manage')
        ->name('users.index');
    Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])
        ->middleware('permission:user.manage')
        ->name('users.role');
    Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])
        ->middleware('permission:user.manage')
        ->name('users.status');
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])
        ->middleware('permission:user.manage')
        ->name('users.reset');
    Route::get('/invites', [InviteController::class, 'index'])
        ->middleware('permission:user.invite')
        ->name('invites.index');
    Route::post('/invites', [InviteController::class, 'store'])
        ->middleware('permission:user.invite')
        ->name('invites.store');
    Route::post('/invites/{invite}/resend', [InviteController::class, 'resend'])
        ->middleware('permission:user.invite')
        ->name('invites.resend');
    Route::post('/invites/{invite}/revoke', [InviteController::class, 'revoke'])
        ->middleware('permission:user.invite')
        ->name('invites.revoke');
});

Route::middleware(['auth', 'role:Owner'])->group(function () {
    Route::get('/permissions', [RolePermissionController::class, 'index'])
        ->middleware('permission:permissions.manage')
        ->name('permissions.index');
    Route::patch('/permissions/{role}', [RolePermissionController::class, 'update'])
        ->middleware('permission:permissions.manage')
        ->name('permissions.update');
});

require __DIR__.'/auth.php';
