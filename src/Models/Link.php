<?php

namespace AmoCRM\Models;

/**
 * Class Link
 *
 * Класс модель для работы со Связями
 *
 * @package AmoCRM\Models
 * @author alexk984 <alexk984@gmail.com>
 * @link https://github.com/dotzero/amocrm-php
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Link extends Base
{
    /**
     * @var array Список доступный полей для модели (исключая кастомные поля)
     */
    protected $fields = [
        'from',
        'from_id',
        'to',
        'to_id',
        'from_catalog_id',
        'to_catalog_id',
        'quantity',
    ];

    /**
     * Список связей
     *
     * Метод для получения списка сделок с возможностью фильтрации и постраничной выборки.
     * Ограничение по возвращаемым на одной странице (offset) данным - 500 сделок
     *
     * @link https://developers.amocrm.ru/rest_api/leads_list.php
     * @param array $parameters Массив параметров к amoCRM API
     * @param null|string $modified Дополнительная фильтрация по (изменено с)
     * @return array Ответ amoCRM API
     */
    public function apiList($parameters, $modified = null)
    {
        $response = $this->getRequest('/private/api/v2/json/links/list', $parameters, $modified);

        return isset($response['leads']) ? $response['leads'] : [];
    }

    /**
     * Добавление связи
     *
     * Метод позволяет добавлять связи по одной или пакетно
     *
     * @link https://developers.amocrm.ru/rest_api/links/set.php
     * @param array $leads Массив связей для пакетного добавления
     * @return int|array Уникальный идентификатор сделки или массив при пакетном добавлении
     */
    public function apiAdd($leads = [])
    {
        if (empty($leads)) {
            $leads = [$this];
        }

        $parameters = [
            'links' => [
                'link' => [],
            ],
        ];

        foreach ($leads AS $lead) {
            $parameters['links']['link'][] = $lead->getValues();
        }

        $response = $this->postRequest('/private/api/v2/json/links/set', $parameters);

        if (isset($response['links']['link'])) {
            $result = array_map(function($item) {
                return $item;
            }, $response['links']['link']);
        } else {
            return [];
        }

        return count($leads) == 1 ? array_shift($result) : $result;
    }
}
