<?php

class Application_Model_Email
{

    private $table = null;

    public function getTable()
    {
        if (is_null($this->table)) {
            $this->table = new Application_Model_DbTable_Email();
        }
        return $this->table;
    }

    public function select($where = array(), $order = null, $limit = null)
    {
        $select = $this->getTable()->select()->order($order)->limit($limit);

        foreach ($where as $coluna => $valor) :
            $select->where($coluna, $valor);
        endforeach;

//        xd($select->assemble());
        return $this->getTable()->fetchAll($select)->toArray();
    }

    public function find($id)
    {
        return $this->getTable()->find($id)->current();
    }

    public function buscarEmails($where = array(), $order = null, $limit = null)
    {

        $select = $this->getTable()->select();
        $select->setIntegrityCheck(false);

        $select->from(array('e' => 'CORPORATIVO.S_EMAIL'),
            array('e.ID_EMAIL',
                'dsEmail' => 'e.DS_EMAIL',
                'e.ID_TIPO_EMAIL',
                'e.ST_EMAIL_PRINCIPAL')
        );

        $select->joinInner(array('te' => 'CORPORATIVO.S_TIPO_EMAIL'), 'e.ID_TIPO_EMAIL = te.ID_TIPO_EMAIL',
            array('te.DS_TIPO_EMAIL')
        );

        if ($where) {
            foreach ($where as $coluna => $valor) :
                $select->where($coluna, $valor);
            endforeach;
        }

        $select->order($order);
        $select->limit($limit);

//        xd($select->assemble());

        return $this->getTable()->fetchAll($select);
    }

    public function insert(array $request)
    {
        return $this->getTable()->createRow()->setFromArray($request)->save();
    }

    public function update(array $request, $id)
    {
        if (is_array($id)) {
            $where = $id;
        } else {
            $where["ID_EMAIL = ?"] = $id;
        }
        return $this->getTable()->update($request, $where);
    }

    public function delete($id)
    {
        return $this->getTable()->find($id)->current()->delete();
    }

    public function deleteAll($where = array())
    {
        if ($where) {
            return $this->getTable()->delete('', $where);
        }
    }

    public function enviarEmail($email, $assunto, $texto, $perfil = 'PerfilGrupoValeCultura')
    {
        /**
         * @todo A procedure não está funcionando nos ambientes de homologação e produção,
         *       por isso está sendo comentado o trecho e sendo feito o envio de email pela aplicação.
         */
//        $sql = "EXEC msdb.dbo.sp_send_dbmail
//                @profile_name          = 'PerfilGrupoValecultura'
//                ,@recipients           = '" . htmlspecialchars($email) . "'
//                ,@body                 = '" . $texto . "'
//                ,@body_format          = 'HTML'
//                ,@subject              = '" . $assunto . "'
//                ,@exclude_query_output = 1;";
//
//        try {
//            $this->getTable()->getAdapter()->query($sql);
//            return true;
//        } catch (Exception $exc) {
//            x($sql);
//            xd($exc->getMessage());
//            return true;
//        }

        $configEmail = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('email');

        $config = array(//'ssl' => 'tls',
            'auth' => $configEmail['smtp-auth'],
            'username' => $configEmail['smtp-username'],
            'password' => $configEmail['smtp-password'],
            'port' => $configEmail['smtp-port']);

        try {
            $transport = new Zend_Mail_Transport_Smtp($configEmail['smtp-host'], $config);

            $mail = new Zend_Mail();
            $mail->setBodyHtml($texto);
            $mail->addTo($email);
            $mail->setFrom($configEmail['smtp-from-email']);
            $mail->setSubject($assunto);
            $mail->send($transport);

            return true;
        } catch (Exception $exc) {
            xd($exc->getMessage());
            return false;
        }
    }
}