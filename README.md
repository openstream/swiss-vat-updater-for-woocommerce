# Swiss VAT updater for WooCommerce
WordPress plugin to automatically update Swiss VAT on January 1st, 2024.

If you want to test this prior to January 1st, you can use the following constant in `wp-config.php`:

```define( 'WC_SWISS_VAT_ADJUST_DATE', '2023-MM-DD');```

Replace `MM` with the month, e.g. 12 and `DD` with the day, e.g. 04. Please note that you can't use today's date, it has to be at least one day after today in order to be triggered correctly.

## Known problems
* Plugin throws an error on multisites if enabled network-wide.

## Links to further information
* [Federal Tax Administration FTA](https://www.estv.admin.ch/estv/en/home/value-added-tax/vat-rates-switzerland.html)
* Openstream blog post: [Erhöhung der Mehrwertsteuersätze in der Schweiz
](https://www.openstream.ch/erhoehung-mwst-2024/)
