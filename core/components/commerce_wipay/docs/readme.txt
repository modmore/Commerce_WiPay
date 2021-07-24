Wipay for Commerce
------------------------

WiPay payments integration to allow Commerce to accept payments through WiPay.

To use the sandbox for testing:

- On the payment method, check the "Use Sandbox" checkbox.
- Account Number should be set to 1234567890
- Api Key should be set to 123
- Select the closest API endpoint for your server. Default is Trinidad and Tobago.

Example test card accepted: 4111111111111111 / rejected: 4666666666662222 / more via https://wipaycaribbean.com/credit-card-documentation/

Important: For the return from WiPay to work, you **must** be using friendly URLs in MODX. Otherwise, the redirect from WiPay back to the Checkout may fail.
It appears that the API also checks that the response_url is valid so it won't return on a local environment. Make sure the return_url is accessible from the internet.
