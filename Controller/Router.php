<?php
/**
 * Workshop_Router extension
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category  Workshop
 * @package   Workshop_Router
 * @copyright Copyright (c) 2017
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Workshop\Router\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\Action\Redirect;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Url;

class Router implements RouterInterface
{
    const MATCH_URL_KEY = 'hello-world.html';
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Event manager
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * Response
     * @var \Magento\Framework\App\ResponseInterface|\Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     * @var bool
     */
    protected $dispatched;

    /**
     * @param ActionFactory $actionFactory
     * @param ManagerInterface $eventManager
     * @param ResponseInterface $response
     */
    public function __construct(
        ActionFactory $actionFactory,
        ManagerInterface $eventManager,
        ResponseInterface $response
    ) {
        $this->actionFactory    = $actionFactory;
        $this->eventManager     = $eventManager;
        $this->response         = $response;
    }

    /**
     * Validate and Match News Author and modify request
     *
     * @param \Magento\Framework\App\RequestInterface|\Magento\Framework\HTTP\PhpEnvironment\Request $request
     * @return ActionInterface
     */
    public function match(RequestInterface $request)
    {
        if (!$this->dispatched) {
            $urlKey = trim($request->getPathInfo(), '/');

            /** @var Object $condition */
            $condition = new DataObject(['url_key' => $urlKey, 'continue' => true]);
            $this->eventManager->dispatch(
                'workshop_faq_controller_router_match_before',
                ['router' => $this, 'condition' => $condition]
            );
            $urlKey = $condition->getUrlKey();
            if ($condition->getRedirectUrl()) {
                $this->response->setRedirect($condition->getRedirectUrl());
                $request->setDispatched(true);
                return $this->actionFactory->create(Redirect::class);
            }
            if (!$condition->getContinue()) {
                return null;
            }
            if ($urlKey == self::MATCH_URL_KEY) {
                $request->setModuleName('workshop_router');
                $request->setControllerName('index');
                $request->setActionName('index');
                $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $urlKey);
                $this->dispatched = true;
//                var_dump($request->getModuleName());exit;
                return $this->actionFactory->create(Forward::class);
            }
        }
        return null;
    }


}
