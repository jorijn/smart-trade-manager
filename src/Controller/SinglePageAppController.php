<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SinglePageAppController extends AbstractController
{
    /**
     * @Route("/{vueRouting}", name="entrypoint", requirements={"vueRouting"="^(?!api).+"}, defaults={"vueRouting": null})
     */
    public function index()
    {
        return $this->render('spa/entrypoint.html.twig');
    }
}
