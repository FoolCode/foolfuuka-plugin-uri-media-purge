<?php

namespace Foolz\Foolframe\Controller\Admin\Plugins;

use Foolz\Foolframe\Model\Validation\ActiveConstraint\Trim;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class URIMediaPurge extends \Foolz\Foolframe\Controller\Admin
{
    protected $purge_service;

    public function before()
    {
        parent::before();

        $this->purge_service = $this->getContext()->getService('foolfuuka-plugin.uri_media_purge');
        $this->param_manager->setParam('controller_title', 'URI Media Purge');
    }

    public function security()
    {
        return $this->getAuth()->hasAccess('maccess.mod');
    }

    function structure()
    {
        return [
            'open' => [
                'type' => 'open',
            ],
            'foolfuuka.plugin.uri_media_purge.uris' => [
                'type' => 'textarea',
                'label' => _i('Media URIs'),
                'help' => _i(''),
                'class' => 'span8',
                'validation' => [new Trim()]
            ],
            'separator-2' => [
                'type' => 'separator-short'
            ],
            'submit' => [
                'type' => 'submit',
                'class' => 'btn-primary',
                'value' => _i('Submit')
            ],
            'close' => [
                'type' => 'close'
            ],
        ];
    }

    function action_manage()
    {
        $this->param_manager->setParam('method_title', 'Manage');

        $data['form'] = $this->structure();

        if ($this->getPost()) {
            $this->purge_service->process($this->getPost('foolfuuka,plugin,uri_media_purge,uris'));
        }
        $this->builder->createPartial('body', 'form_creator')->getParamManager()->setParams($data);

        return new Response($this->builder->build());
    }
}
