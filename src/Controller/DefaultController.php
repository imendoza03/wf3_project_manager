<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class DefaultController
{
    public function homePage(Environment $twig)
    {
        $twigTemplate = $twig->render('/Default/homepage.html.twig');
        return new Response($twigTemplate);
    }
}

