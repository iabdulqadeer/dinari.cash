<?php

/*
 * ==========================================================
 * STRIPE.PHP
 * ==========================================================
 *
 * Process Stripe payments
 *
 */

header('Content-Type: application/json');
$raw = file_get_contents('php://input');
$response = json_decode($raw, true);

if ($response && empty($response['error']) && !empty($response['id'])) {
    require('functions.php');
    $response = bxc_stripe_curl('events/' . $response['id'], 'GET');
    $data = $response['data']['object'];
    if (isset($data['metadata']) && isset($data['metadata']['source']) && $data['metadata']['source'] === 'boxcoin') {
        if (BXC_CLOUD) {
            if (isset($data['metadata']['cloud'])) {
                $_POST['cloud'] = $data['metadata']['cloud'];
                bxc_cloud_load();
                bxc_cloud_spend_credit($data['amount_total'] / 100, $transaction['currency']);
            } else {
                die();
            }
        }
        switch ($response['type']) {
            case 'checkout.session.completed':
                $transaction = bxc_transactions_get($data['client_reference_id']);
                if ($transaction) {
                    bxc_transactions_complete($transaction, $data['amount_total'] / 100, $data['customer']);
                }
                break;
        }
    }
}

?>