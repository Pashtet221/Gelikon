Gelikon Theme
=============

Установка
---------
1. Загрузите папку темы в /wp-content/themes/ или установите zip через админку.
2. Активируйте тему Gelikon.
3. Установите WooCommerce.
4. Для редактирования главной через ACF установите Advanced Custom Fields.
5. Создайте страницу, назначьте ее главной в Настройки -> Чтение.

Главная страница через ACF
-------------------------
Тема больше НЕ использует Customizer для контента главной.
Контент главной выводится из полей ACF, привязанных к странице, назначенной главной.

Рекомендуемые поля ACF:

Текстовые поля:
- home_products_title
- home_trust_title
- home_blog_title
- home_blog_link_text
- home_reviews_title

Баннеры (для каждого 1-4):
- home_banner_1_title
- home_banner_1_text
- home_banner_1_button_text
- home_banner_1_link
- home_banner_1_image
...
- home_banner_4_title
- home_banner_4_text
- home_banner_4_button_text
- home_banner_4_link
- home_banner_4_image

Repeater для блока доверия:
- home_trust_items
  - title
  - text

Repeater для отзывов:
- home_reviews
  - name
  - text

Популярные товары:
- home_popular_products
  Тип поля: Relationship или Post Object
  Разрешенный post type: product

Если ACF-поля пустые, тема покажет безопасные значения по умолчанию.

Customizer
----------
В Customizer оставлены только общие настройки темы:
- цвета
- размеры и радиусы
- телефоны в шапке
- общий бейдж товара

SCSS
----
Основные переменные находятся в:
assets/scss/_variables.scss
