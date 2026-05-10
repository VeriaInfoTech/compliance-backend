<?php

namespace Risk\Service;

use Risk\Repository\FormRepositoryInterface;

class FormService implements ServiceInterface
{
    /* @var FormRepositoryInterface */
    protected FormRepositoryInterface $formRepository;

    /**
     * @param FormRepositoryInterface $formRepository
     */
    public function __construct(
        FormRepositoryInterface $formRepository
    ) {
        $this->formRepository = $formRepository;
    }

    public function saveInput($params)
    {
        // Set result
        $result = [
            'record' => [],
            'data'   => [],
        ];

        // Set record params
        $recordParams = [
            'user_id'     => $params['user_id'],
            'company_id'  => $params['user_id'],
            'form_id'     => $params['form_id'],
            'status'      => 1,
            'time_create' => time(),
            'time_update' => time(),
        ];

        // insert record
        $recordRow        = $this->formRepository->addFormRecord($recordParams);
        $result['record'] = $this->canonizeRecord($recordRow);

        // insert record
        foreach ($params['data'] as $data) {
            $data = $this->prepareData($data);

            $dataParams = [
                'value'       => $data['value'],
                'description' => '',
                'record_id'   => $result['record']['id'],
                'user_id'     => $params['user_id'],
                'company_id'  => $params['user_id'],
                'form_id'     => $params['form_id'],
                'element_id'  => $data['element_id'],
                'status'      => 1,
                'time_create' => time(),
                'time_update' => time(),
            ];

            $dataRow          = $this->formRepository->addFormData($dataParams);
            $result['data'][] = $this->canonizeData($dataRow);
        }

        return $result;
    }

    public function getRecordData($params)
    {
        // Set params
        $recordParams = [
            'page'   => 1,
            'limit'  => 1,
            'order'  => ['time_create DESC', 'id DESC'],
            'offset' => 0,
        ];

        // Get record
        $recordList = [];
        $rowSet = $this->formRepository->getFormRecordList($recordParams);
        foreach ($rowSet as $row) {
            $recordList[] = $this->canonizeRecord($row);
        }
        $record = !empty($recordList) ? array_shift($recordList) : [];

        // Get data
        $dataList = [];
        if (!empty($record)) {

            // Set params
            $dataParams = [
                'page'   => 1,
                'limit'  => 1000,
                'order'  => ['time_create DESC', 'id DESC'],
                'offset' => 0,
            ];

            // Get data
            $rowSet = $this->formRepository->getFormDataList($dataParams);
            foreach ($rowSet as $row) {
                $dataList[] = $this->canonizeData($row);
            }
        }

        return [
            'record' => $record,
            'data'   => $dataList,
        ];
    }

    public function prepareData($data)
    {
        // Set temp value
        $data['value'] = json_encode(['old' => $data['old'], 'new' => $data['new']]);

        // Set temp element
        $data['element_id'] = 0;

        return $data;
    }

    public function canonizeInventory($inventory)
    {
        if (empty($inventory)) {
            return [];
        }

        if (is_object($inventory)) {
            $inventory = [
                'id'          => $inventory->getId(),
                'title'       => $inventory->getTitle(),
                'slug'        => $inventory->getSlug(),
                'status'      => $inventory->getStatus(),
                'time_create' => $inventory->getTimeCreate(),
                'time_update' => $inventory->getTimeUpdate(),
            ];
        }

        return $inventory;
    }

    public function canonizeElement($element)
    {
        if (empty($element)) {
            return [];
        }

        if (is_object($element)) {
            $element = [
                'id'            => $element->getId(),
                'code'          => $element->getCode(),
                'title'         => $element->getTitle(),
                'description'   => $element->getDescription(),
                'value'         => $element->getValue(),
                'type'          => $element->getType(),
                'required'      => $element->getRequired(),
                'display_order' => $element->getDisplayOrder(),
                'status'        => $element->getStatus(),
                'time_create'   => $element->getTimeCreate(),
                'time_update'   => $element->getTimeUpdate(),
            ];
        }

        return $element;
    }

    public function canonizeLink($link)
    {
        if (empty($link)) {
            return [];
        }

        if (is_object($link)) {
            $link = [
                'id'         => $link->getId(),
                'form_id'    => $link->getFormId(),
                'element_id' => $link->getElementId(),
                'status'     => $link->getStatus(),
            ];
        }

        return $link;
    }

    public function canonizeRecord($record)
    {
        if (empty($record)) {
            return [];
        }

        if (is_object($record)) {
            $record = [
                'id'          => $record->getId(),
                'user_id'     => $record->getUserId(),
                'company_id'  => $record->getCompanyId(),
                'form_id'     => $record->getFormId(),
                'status'      => $record->getStatus(),
                'time_create' => $record->getTimeCreate(),
                'time_update' => $record->getTimeUpdate(),
            ];
        }

        return $record;
    }

    public function canonizeData($data)
    {
        if (empty($data)) {
            return [];
        }

        if (is_object($data)) {
            $data = [
                'id'          => $data->getId(),
                'value'       => $data->getValue(),
                'description' => $data->getDescription(),
                'record_id'   => $data->getRecordId(),
                'user_id'     => $data->getUserId(),
                'company_id'  => $data->getCompanyId(),
                'form_id'     => $data->getFormId(),
                'element_id'  => $data->getElementId(),
                'status'      => $data->getStatus(),
                'time_create' => $data->getTimeCreate(),
                'time_update' => $data->getTimeUpdate(),
            ];
        }

        $data['value'] = json_decode($data['value'], true);

        return $data;
    }
}
