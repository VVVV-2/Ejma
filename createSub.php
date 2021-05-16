<?php 

include "includes/config.php";

$body = json_decode(file_get_contents('php://input'));

if(isset($_SESSION['customer'])){
    
    $customer_id = $_SESSION['customer'];

    # Attach the newly created payment method to the customer.
    try {
        $payment_method = $stripe->paymentMethods->retrieve(
            $body->paymentMethodId
        );
        $payment_method->attach([
            'customer' => $customer_id,
        ]);
    } catch (\Stripe\Exception\CardException $e) {
    return $response->withJson([
        'error' => [
        'message' => $e->getError()->message,
        ]
    ], 400);
    }

    // Create the subscription.
    $subscription = $stripe->subscriptions->create([
        'customer' => $customer_id,
        'default_payment_method' => $payment_method->id,
        'items' => [[
            'price' => $body->priceLookupKey,
        ]],
        'expand' => ['latest_invoice.payment_intent'],
    ]);

    echo json_encode(['subscription' => $subscription]);

};

?>