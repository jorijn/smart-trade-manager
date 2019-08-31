<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class SinglePageAppController
{
    /** @var Environment */
    protected $templating;

    /**
     * @param Environment $templating
     */
    public function __construct(Environment $templating)
    {
        $this->templating = $templating;
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     *
     * @return Response
     */
    public function index(): Response
    {
        return new Response($this->templating->render('spa/entrypoint.html.twig'));
    }
}
