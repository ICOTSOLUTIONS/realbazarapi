<?php

return [
    'jazzcash' => [
        'MERCHANT_ID'      => 'MC51355',
        'PASSWORD'          => 'z70s61hue2',
        'INTEGERITY_SALT' => '0yyx9u5uw1',
        'CURRENCY_CODE'  => 'PKR',
        'VERSION'         => '1.1',
        'LANGUAGE'       => 'EN',
        'MerchantMPIN'       => '0000',

        'WEB_RETURN_URL'  => 'https://real-bazar-web.vercel.app/account/payment/',
        'RETURN_URL'  => 'https://realbazarapi.icotsolutions.com/api/payment/status',
        'TRANSACTION_POST_URL'  => 'https://sandbox.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform/',
        'MOBILE_REFUND_POST_URL'  => 'https://sandbox.jazzcash.com.pk/ApplicationAPI/API/Purchase/domwalletrefundtransaction/',
        'CARD_REFUND_POST_URL'  => 'https://sandbox.jazzcash.com.pk/ApplicationAPI/API/authorize/Refund',
        'STATUS_INQUIRY_POST_URL'  => 'https://payments.jazzcash.com.pk/ApplicationAPI/API/PaymentInquiry/Inquire',
    ]
];
