<?php

use Foolz\FoolFrame\Model\Autoloader;
use Foolz\FoolFrame\Model\Context;
use Foolz\Plugin\Event;

class HHVM_URIMediaPurge
{
    public function run()
    {
        Event::forge('Foolz\Plugin\Plugin::execute#foolz/foolfuuka-plugin-uri-media-purge')
            ->setCall(function ($result) {
                /* @var Context $context */
                $context = $result->getParam('context');
                /** @var Autoloader $autoloader */
                $autoloader = $context->getService('autoloader');

                $autoloader->addClassMap([
                    'Foolz\FoolFrame\Controller\Admin\Plugins\URIMediaPurge' => __DIR__ . '/classes/controller/admin.php',
                    'Foolz\FoolFuuka\Plugins\URIMediaPurge\Model\URIMediaPurge' => __DIR__ . '/classes/model/purge.php'
                ]);

                $context->getContainer()
                    ->register('foolfuuka-plugin.uri_media_purge', 'Foolz\FoolFuuka\Plugins\URIMediaPurge\Model\URIMediaPurge')
                    ->addArgument($context);

                Event::forge('Foolz\FoolFrame\Model\Context::handleWeb#obj.afterAuth')
                    ->setCall(function ($result) use ($context) {
                        // don't add the admin panels if the user is not an admin
                        if ($context->getService('auth')->hasAccess('maccess.admin')) {
                            $context->getRouteCollection()->add(
                                'foolfuuka.plugin.uri_media_purge.admin', new \Symfony\Component\Routing\Route(
                                    '/admin/plugins/uri_media_purge/{_suffix}',
                                    [
                                        '_suffix' => 'manage',
                                        '_controller' => 'Foolz\FoolFrame\Controller\Admin\Plugins\URIMediaPurge::manage'
                                    ],
                                    [
                                        '_suffix' => '.*'
                                    ]
                                )
                            );

                            Event::forge('Foolz\FoolFrame\Controller\Admin::before#var.sidebar')
                                ->setCall(function ($result) {
                                    $sidebar = $result->getParam('sidebar');
                                    $sidebar[]['plugins'] = [
                                        'content' => ['uri_media_purge/manage' => ['level' => 'admin', 'name' => 'URI Media Purge', 'icon' => 'icon-leaf']]
                                    ];
                                    $result->setParam('sidebar', $sidebar);
                                });
                        }
                    });
            });
    }
}

(new HHVM_URIMediaPurge())->run();
