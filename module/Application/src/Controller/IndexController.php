<?php

declare(strict_types=1);

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
//use Pi\Core\Application\Loader;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {

        die;

        /* $pi = new Loader();

        $input        = ['a', 'b', 'c'];
        $input        = json_encode($input);
        $encryption   = $pi->service('encryption');
        $encryptInput = $encryption->process($input, 'encrypt');
        $decryptInput = $encryption->process($encryptInput, 'decrypt'); */

        /* echo '<pre>';
        var_dump($encryptInput);
        var_dump($decryptInput);
        var_dump(json_decode($decryptInput, true));
        echo '</pre>'; */

        /* $file   = $pi->service('file');
        $size   = $file->transformSize(21312321);
        $copy   = $file->copy('/var/www/html/local/laminas/data/log/hhhh.log', '/var/www/html/local/laminas/data/log/hhhh2.log');
        $mkdir  = $file->mkdir('/var/www/html/local/laminas/data/log/ds');
        $exists = $file->exists('/var/www/html/local/laminas/data/log/hhhh.log');
        $flush  = $file->flush('/var/www/html/local/laminas/data/log/ds'); */

        /* echo '<pre>';
        var_dump($size);
        var_dump($copy);
        var_dump($mkdir);
        var_dump($exists);
        var_dump($flush);
        echo '</pre>'; */


        /* $authentication = $pi->service('authentication');
        $authentication->setStrategy('Local', ['a', 'b', 'c', 'd']);
        $result = $authentication->authenticate(
            'admin',
            '123456',
            'identity'
        );
        //$result = $this->verifyResult($result);
        var_dump($authentication);
        var_dump($result);

        die; */

        return new ViewModel();
    }
}
