CREATE TABLE `parser`.`product`
(
  `id`           INT(11) UNSIGNED      NOT NULL AUTO_INCREMENT,
  `url_product`  VARCHAR(255)          NOT NULL,
  `h1`           VARCHAR(255)          NULL,
  `content_ru`   TEXT                  NULL COMMENT 'Описание товаров на ru',
  `content_pl`   TEXT                  NULL COMMENT 'Описание товаров на pl',
  `content_en`   TEXT                  NULL COMMENT 'Описание товаров на en',
  `img`          VARCHAR(255)          NULL,
  `price`        INT                   NULL,
  `status`       BOOLEAN               NULL,
  `tags`         VARCHAR(255)          NULL COMMENT '(Теги (Метки))',
  `attribute`    VARCHAR(255)          NULL COMMENT 'Атрибуты(Опции, Параметры)',
  `language`     ENUM ('RU','EN','PL') NULL DEFAULT 'PL',
  `data_parsing` TIMESTAMP             NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`url_product`)
) ENGINE = InnoDB
  CHARSET = utf8
  COLLATE utf8_general_ci COMMENT = 'Таблица продуктов';