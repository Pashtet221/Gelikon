# Переход на оплату Т-Банка

`inc/order-payment-flow.php` запускает `woocommerce_receipt_tbank` на
`template_redirect`, до HTML страницы. Использует внутренний ID из endpoint,
проверяет ключ заказа, способ оплаты и необходимость платежа. Повторяет условия
receipt-ветки WooCommerce; GET с `pay_for_order` остаётся обычной формой оплаты,
POST не запускается этим обработчиком.

Если обработчик отсутствует, восстанавливает `receipt_page` зарегистрированного
шлюза. При редиректе плагина на HTTPS-домен Т-Банка завершает запрос до вывода
страницы. Если заголовки уже отправлены, показывает ссылку и переход через
браузер. При отказе сохраняет вывод плагина и показывает notice; receipt-шаблон
получает сохранённый вывод, а не повторный вызов создания платежа.

Плагин не изменяется. Новая кастомная нумерация и GL-префикс выключены.
Исторические соответствия заказов/платежей сохраняются. Журнал раннего
обработчика: WooCommerce → Статус → Журналы → `gelikon-payment-flow`.
Ответ банка в штатном плагине по-прежнему находится в его `log.php`.

## Локальная проверка HTTP

Только на локальной установке с фиктивными заказами. Подготовка и mu-plugin
проверяют localhost/127.0.0.1. Провайдер замещается, реальная оплата не выполняется.

```sh
cd /workspace/Gelikon
docker compose -f /workspace/.gelikon-env/compose.yaml exec -T wordpress \
  php /opt/wp-cli.phar --allow-root eval-file \
  /var/www/html/wp-content/themes/gelikon/tests/payment-flow-http-prepare.php
docker cp gelikon-env-wordpress-1:/tmp/payment-flow-fixture.json /workspace/.gelikon-env/payment-flow-fixture.json
docker cp tests/payment-flow-http-fixture.php gelikon-env-wordpress-1:/var/www/html/wp-content/mu-plugins/gelikon-payment-flow-test.php
docker cp tests/payment-flow-broken-receipt.php gelikon-env-wordpress-1:/tmp/payment-flow-broken-receipt.php
# Если локальный WooCommerce показывает Coming soon, временно отключить его
# и после проверки вернуть исходное значение.
docker compose -f /workspace/.gelikon-env/compose.yaml exec -T wordpress \
  php /opt/wp-cli.phar --allow-root option update woocommerce_coming_soon no
python3 tests/payment-flow-http-test.py /workspace/.gelikon-env/payment-flow-fixture.json
```

Проверяет стандартный и неполный receipt-шаблоны, отсутствующий обработчик,
HTTP 302, ровно один вызов шлюза, неверный ключ, обычную pay-for-order форму,
видимый отказ и резервный переход при отправленных заголовках.
После проверки удалить тестовый заказ (ID из fixture), временный mu-plugin,
файл fixture с ключом и `/tmp/payment-flow-*`, восстановить видимость локального
магазина. Не устанавливать тестовый mu-plugin на рабочий сайт.

Стандартный локальный checkout до исправления уже вызывал обработчик и возвращал
302. Сбой рабочего сайта без доступа к его текущей конфигурации не воспроизведён;
отсутствие вызова воспроизведено неполным шаблоном, затем исправлено ранним запуском.
Это проверка маршрута с замещённым шлюзом, а не подтверждение живой оплаты.
