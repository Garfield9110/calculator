<?php

namespace App\Controller;

use App\Service\Calculator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class DefaultController
 *
 * @package App\Controller
 */
class DefaultController extends AbstractController
{
    /**
     * @param Request $request
     * @param Calculator $calculator
     *
     * @return Response
     */
    public function index(Request $request, Calculator $calculator): Response
    {
        //expression to be calculated submitted by the calculator
        $postInput = $request->request->get('input');

        $calResult = '';
        if (!empty($postInput)) {
            //we input the expression into the calculator and get the value
            $calResult = $calculator->calculate($postInput);
        }
        $errors = $calculator->getErrors();

        return $this->render('calculator.html.twig', [
            'calcResult' => $calResult,
            'baseInput' => $calculator->getInput(),
            'errors' => $errors,
        ]);
    }
}