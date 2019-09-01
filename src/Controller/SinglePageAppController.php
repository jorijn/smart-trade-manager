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
    /** @var float */
    protected $portfolioLossThreshold;

    /**
     * @param Environment $templating
     * @param float       $portfolioLossThreshold
     */
    public function __construct(Environment $templating, float $portfolioLossThreshold)
    {
        $this->templating = $templating;
        $this->portfolioLossThreshold = $portfolioLossThreshold;
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
        return new Response($this->templating->render('spa/entrypoint.html.twig', [
            'options' => [
                'portfolio_loss_threshold' => $this->portfolioLossThreshold,
            ],
        ]));
    }
}
