<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SinglePageAppController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('spa/entrypoint.html.twig');
    }
}
