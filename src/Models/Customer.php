<?php

namespace AmoCRM\Models;

/**
 * Class Customer
 *
 * Класс модель для работы с Покупателями
 *
 * @package AmoCRM\Models
 * @author alexk984 <alexk984@gmail.com>
 * @link https://github.com/dotzero/amocrm-php
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Customer extends Base
{
    /**
     * @var array Список доступный полей для модели (исключая кастомные поля)
     */
    protected $fields = [
        'name',
        'main_user_id',
        'next_price',
        'periodicity',
        'tags',
        'next_date',
        'request_id',
    ];

    /**
     * Сеттер для даты следующей покупки
     *
     * @param string $date Дата в произвольном формате
     * @return $this
     */
    public function setNextDate($date)
    {
        $this->values['next_date'] = strtotime($date);

        return $this;
    }

    /**
     * Сеттер для даты последнего изменения контакта
     *
     * @param string $date Дата в произвольном формате
     * @return $this
     */
    public function setLastModified($date)
    {
        $this->values['last_modified'] = strtotime($date);

        return $this;
    }

    /**
     * Сеттер для списка тегов покупателя
     *
     * @param int|array $value Название тегов через запятую или массив тегов
     * @return $this
     */
    public function setTags($value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        $this->values['tags'] = implode(',', $value);

        return $this;
    }

    /**
     * Список покупателей
     *
     * Метод для получения списка покупателей с возможностью фильтрации и постраничной выборки.
     * Ограничение по возвращаемым на одной странице (offset) данным - 500 контактов.
     *
     * @link https://developers.amocrm.ru/rest_api/customers/list.php
     * @param array $parameters Массив параметров к amoCRM API
     * @param null|string $modified Дополнительная фильтрация по (изменено с)
     * @return array Ответ amoCRM API
     */
    public function apiList($parameters, $modified = null)
    {
        $response = $this->getRequest('/private/api/v2/json/customers/list', $parameters, $modified);

        return isset($response['customers']) ? $response['customers'] : [];
    }

    /**
     * Добавление покупателей
     *
     * Метод позволяет добавлять покупателей по одному или пакетно
     *
     * @link https://developers.amocrm.ru/rest_api/customers/set.php
     * @param array $customers Массив контактов для пакетного добавления
     * @return int|array Уникальный идентификатор контакта или массив при пакетном добавлении
     */
    public function apiAdd($customers = [])
    {
        if (empty($customers)) {
            $customers = [$this];
        }

        $parameters = [
            'customers' => [
                'add' => [],
            ],
        ];

        foreach ($customers AS $customer) {
            $parameters['customers']['add'][] = $customer->getValues();
        }

        $response = $this->postRequest('/private/api/v2/json/customers/set', $parameters);

        if (isset($response['customers']['add'])) {
            $result = array_map(function($item) {
                return $item['id'];
            }, $response['customers']['add']['customers']);
        } else {
            return [];
        }

        return count($customers) == 1 ? array_shift($result) : $result;
    }

    /**
     * Обновление покупателей
     *
     * Метод позволяет обновлять данные по уже существующим покупателям
     *
     * @link https://developers.amocrm.ru/rest_api/customers/set.php
     * @param int $id Уникальный идентификатор покупателя
     * @param string $modified Дата последнего изменения данной сущности
     * @return bool Флаг успешности выполнения запроса
     * @throws \AmoCRM\Exception
     */
    public function apiUpdate($id, $modified = 'now')
    {
        $this->checkId($id);

        $parameters = [
            'customers' => [
                'update' => [],
            ],
        ];

        $customers = $this->getValues();
        $customers['id'] = $id;
        $customers['last_modified'] = strtotime($modified);

        $parameters['customers']['update'][] = $customers;

        $response = $this->postRequest('/private/api/v2/json/customers/set', $parameters);

        return isset($response['customers']) ? true : false;
    }
}
