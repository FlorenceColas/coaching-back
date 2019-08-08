<?php

namespace Authentication;

use Application\Controller\IndexController;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql\Sql;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AuthenticationController extends AbstractActionController
{
    protected $authService;
    protected $config;
    protected $mailService;

    /**
     * @param ZendAuthenticationService $authService
     * @param array $config
     * @param MailService $mailService
     */
    public function __construct(
        ZendAuthenticationService $authService,
        array $config,
        MailService $mailService
    ) {
        $this->authService = $authService;
        $this->config      = $config;
        $this->mailService = $mailService;
    }

    public function blockJwtRecord(string $jwt)
    {
        $authAdapter = new DbAdapter([
            'database'       => $this->config['db_auth']['database'],
            'driver'         => $this->config['db_auth']['driver'],
            'driver_options' => $this->config['db_auth']['driver_options'],
            'hostname'       => $this->config['db_auth']['hostname'],
            'password'       => $this->config['db_auth']['password'],
            'username'       => $this->config['db_auth']['username'],
        ]);

        $sql = new Sql($authAdapter);

        $update = $sql->update('jwt')
            ->set(['status' => User::JWT_STATUS_BLOCKED])
            ->where(['jwt' => $jwt]);

        $update = $sql->buildSqlString($update);
        $authAdapter->query($update, DbAdapter::QUERY_MODE_EXECUTE);
    }

    public function loginAction()
    {
        $error        = [];
        $info         = [];
        $messageError = [];

        $loginForm = new LoginForm();
        $loginForm->setAttribute('action' ,$this->getRequest()->getUri()->__toString());

        $request = $this->getRequest();

        if ($request->isGet()) {
            $params = $this->params()->fromRoute();
            $error         = $params['error'] ?? [];
            $info          = $params['info'] ?? [];
            $originalRoute = $params['original_route'] ?? '';
            if (!empty($originalRoute)) {
                $loginForm->get('controller')->setValue($originalRoute['controller'] ?? '');
                $loginForm->get('action')->setValue($originalRoute['action'] ?? '');
                $loginForm->get('id')->setValue($originalRoute['id'] ?? '');
                $loginForm->get('route')->setValue($originalRoute['route'] ?? '');
            }
        }

        if ($request->isPost()) {
            $data = $this->params()->fromPost();
            $loginForm->setData($data);
            if ($loginForm->isValid()) {
                $auth = $this->authService->authenticateUser($loginForm->get('username')->getValue(),$loginForm->get('password')->getValue());
                if ($auth['valid']) {
                    if (!empty($loginForm->get('controller')->getValue())) {
                        return $this->redirect()->toRoute($loginForm->get('route')->getValue(), [
                            'controller' => $loginForm->get('controller')->getValue(),
                            'action'     => $loginForm->get('action')->getValue(),
                            'id'         => $loginForm->get('id')->getValue(),
                        ]);
                    } else {
                        return $this->redirect()->toRoute('home', [
                            'controller' => IndexController::class,
                            'action'     => 'home',
                        ]);
                    }
                }
                if (!empty($auth['result']['message'])) {
                    $messageError = $auth['result']['message'];
                }
            }
        }

        return new ViewModel([
            'login'              => $loginForm,
            'result'             => $messageError,
            'flashmessageserror' => $error,
            'flashmessagesinfo'  => $info,
        ]);
    }

    public function logoutAction()
    {
        $jwt = $this->authService->getStorage()->read()['jwt'] ?? '';
        $this->blockJwtRecord($jwt);

        $this->authService->clearIdentity();

        return $this->redirect()->toRoute('login', [
            'controller' => AuthenticationController::class,
            'action'     => 'login',
        ]);
    }

    public function loginhelpAction(): ViewModel
    {
        $form = new ContactForm();
        $form->setInputFilter(new ContactFilter());

        $form->setAttribute('action' ,$this->getRequest()->getUri()->__toString());
        $form->get('title')->setValue("Subject: Need help to login to Warehouse Portal");

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $this->params()->fromPost();
            if (isset($data['back']) == 1) {
                return $this->redirect()->toRoute('login', [
                    'controller' => AuthenticationController::class,
                    'action'     => 'login',
                ]);
            } elseif (isset($data['send']) == 1) {
                $form->setData($data);
                if ($form->isValid()) {
                    $message = sprintf('Message from %1$s (email address: %2$s)<br />%3$s',
                        $form->get('name')->getValue(),
                        $form->get('email')->getValue(),
                        $form->get('message')->getValue()
                    );
                    $this->mailService->send(
                        $form->get('title')->getValue(),
                        $message
                    );

                    return $this->redirect()->toRoute('login', [
                        'controller' => AuthenticationController::class,
                        'action'     => 'login',
                        'info'       => ['Your message has been sent'],
                    ]);
                }
            }
        }

        return new ViewModel([
            'form' => $form,
        ]);
    }
}
