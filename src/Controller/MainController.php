<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\TelegramApiService;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MainController extends AbstractController
{
    public function __construct(protected TelegramApiService $telegramApiService)
    {
        
    }
    
    #[Route('/', name: 'app_main')]
    public function index(Request $request): Response
    {
        if ($request->query->get('logout')) {
            setcookie('tg_user', '');
        }

        return $this->render('main/index.html.twig', [
            'telegramData' => $this->telegramApiService->getParameters(),
        ]);
    }

    #[Route('/test', name: 'app_test')]
    public function test(Request $request): Response
    {
        return $this->render('base.html.twig', [
          //
        ]);
    }

    #[Route('/telegram/auth', name: 'app_telegram_auth')]
    public function handleTelegramApi(Request $request): Response
    {
        $data = $this->telegramApiService->checkTelegramAuth($request->query->all());

        if ($data) {
            $this->telegramApiService->saveTelegramUserData($data);
        }

        $tg_user = $this->telegramApiService->getTelegramUserData($request);

        if ($tg_user) {
          $this->telegramApiService->addTelegramUserIntoDb($tg_user);

          return $this->render('profile.html.twig', [
            'tgData'       => $tg_user,
            'telegramData' => $this->telegramApiService->getParameters(),
          ]);
        }
      
        return $this->render('main/index.html.twig');
  }
}
