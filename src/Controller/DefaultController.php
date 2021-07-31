<?php

namespace App\Controller;

use App\Service\Calculator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class DefaultController
 *
 * @package App\Controller
 */
class DefaultController extends AbstractController
{
	// /**
	//  * @return Response
	//  */
	// public function index(): Response {
	// 	return $this->render('calculator.html.twig');
	// }

	/**
	 * @param Calculator $calculator
	 *
	 * @return Response
	 */
	public function index(Calculator $calculator): Response {

		$postInput = '-2-11+21/-2/3*5';
		$calResult = $calculator->calculate($postInput);
		$errors = $calculator->getErrors();

		$success = empty($errors);

		// echo json_encode(['success' => $success, 'result' => $calResult, 'errors' => $errors])
		return $this->render('calculator.html.twig', [
			'success' => $success,
			'calcResult' => $calResult,
			'erros' => $errors,
		]);
	}
}