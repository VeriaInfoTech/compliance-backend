<?php

namespace Risk\Service;

use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class SpreadsheetService implements ServiceInterface
{
    /* @var FormService */
    protected FormService $formService;

    /**
     * @param FormService $formService
     */
    public function __construct(
        FormService $formService
    ) {
        $this->formService = $formService;
    }

    public function readFile($params)
    {
        switch ($params['extension']) {
            case 'csv':
                $reader = new Csv();
                $reader->setInputEncoding('UTF-8');
                $reader->setDelimiter(',');
                $reader->setEnclosure('"');
                $reader->setSheetIndex(0);
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($params['path']);
                $dataSheet   = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                break;

            case 'xlsx':
                $reader = new Xlsx();
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($params['path']);
                $dataSheet   = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                break;

            case 'xls':
                $reader = new Xls();
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($params['path']);
                $dataSheet   = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                break;

            default:
                $data = [];
                break;
        }

        // Set array keys by first row title
        $i    = 1;
        $data = [];
        $keys = array_shift($dataSheet);
        $keys = ['name', 'code', 'new', 'old'];
        foreach ($dataSheet as $dataSingle) {
            $dataSingle = array_combine($keys, $dataSingle);
            foreach ($dataSingle as $key => $value) {
                if (!empty($key) && !empty($value)) {
                    $data[$i][$key] = $value;
                }
            }
            $i++;
        }

        return $data;
    }
}
