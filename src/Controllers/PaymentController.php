<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Models\Payment as PaymentModel;
use App\Models\Restaurant;

class PaymentController extends Controller
{
    private $authService;
    private $paymentModel;
    private $restaurantModel;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->paymentModel = new PaymentModel();
        $this->restaurantModel = new Restaurant();
    }

    public function showPayment($restaurantId)
    {
        $restaurant = $this->restaurantModel->find($restaurantId);
        
        if (!$restaurant || $restaurant['owner_id'] != $this->authService->userId()) {
            $this->setFlash('error', 'Restaurante no encontrado.');
            return $this->redirect('/dashboard');
        }

        $config = require __DIR__ . '/../../config/app.php';
        $amount = $config['additional_restaurant_price'];

        $this->view('payments.show', [
            'title' => 'Pago de Restaurante - GRG',
            'restaurant' => $restaurant,
            'amount' => $amount
        ]);
    }

    public function process()
    {
        $restaurantId = $this->input('restaurant_id');
        $restaurant = $this->restaurantModel->find($restaurantId);
        
        if (!$restaurant || $restaurant['owner_id'] != $this->authService->userId()) {
            $this->setFlash('error', 'No autorizado.');
            return $this->redirect('/dashboard');
        }

        $config = require __DIR__ . '/../../config/app.php';
        $amount = $config['additional_restaurant_price'];

        // Create payment record
        $paymentId = $this->paymentModel->create([
            'user_id' => $this->authService->userId(),
            'restaurant_id' => $restaurantId,
            'amount' => $amount,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_method' => $this->input('payment_method', 'credit_card')
        ]);

        // STUB: In production, integrate with real payment gateway
        // For now, simulate successful payment
        $transactionId = 'TXN_' . strtoupper(uniqid());
        
        $this->paymentModel->markAsPaid($paymentId, $transactionId, [
            'stub' => true,
            'message' => 'Simulated payment - integrate with real gateway'
        ]);

        // Update restaurant payment status
        $this->restaurantModel->update($restaurantId, [
            'payment_status' => 'paid',
            'requires_payment' => false
        ]);

        $this->setFlash('success', 'Pago procesado exitosamente. Tu restaurante está ahora activo.');
        return $this->redirect('/grg/owner/restaurants/' . $restaurantId);
    }

    public function success()
    {
        $this->view('payments.success', [
            'title' => 'Pago Exitoso - GRG'
        ]);
    }

    public function cancel()
    {
        $this->view('payments.cancel', [
            'title' => 'Pago Cancelado - GRG'
        ]);
    }
}
