# Lovat_Api

## Description
Magento 2 REST API Lovat Api

## Plugin Installation

Download the extension as a ZIP file from this repository or install our module from [Composer](https://getcomposer.org/), using the following command:

```composer require lovat/module-lovat```

then run the following commands
```
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:clean
```

OR

```
php bin/magento setup:di:update
php bin/magento setup:di:compile
php bin/magento cache:clean
```

Next, create a new integration for API requests. In more detail how to create an integration is described [Здесь](https://www.mageplaza.com/kb/how-to-create-new-api-information-for-integration-magento-2.html)