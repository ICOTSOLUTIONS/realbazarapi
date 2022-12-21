<?php

return [
    'jazzcash' => [
        'MERCHANT_ID'      => '99335955',
        'PASSWORD'          => 'xz5z30e245',
        'INTEGERITY_SALT' => '2xy91y8d9w',
        'CURRENCY_CODE'  => 'PKR',
        'VERSION'         => '2.0',
        'LANGUAGE'       => 'EN',
        'MerchantMPIN'       => '0000',

        'WEB_RETURN_URL'  => 'https://realbazar.pk/account/payment/',
        'RETURN_URL'  => 'https://api.realbazar.pk/api/payment/status',
        'TRANSACTION_POST_URL'  => 'https://payments.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform/',
        'MOBILE_REFUND_POST_URL'  => 'https://payments.jazzcash.com.pk/ApplicationAPI/API/Purchase/domwalletrefundtransaction/',
        'CARD_REFUND_POST_URL'  => 'https://payments.jazzcash.com.pk/ApplicationAPI/API/authorize/Refund',
        'STATUS_INQUIRY_POST_URL'  => 'https://payments.jazzcash.com.pk/ApplicationAPI/API/PaymentInquiry/Inquire',
    ]
];
