<?php

namespace App\Service;

use function Symfony\Component\Translation\t;

/**
 * Class Calculator
 *
 * @inspiration https://gist.github.com/ircmaxell/1232629
 * @package App\Service
 */
class Calculator
{
	/**
	 * @var string
	 */
	public string $input = "";

	/**
	 * @var float|int
	 */
	public float $output = 0;

	/**
	 * @var array
	 */
	public array $errors = [];

	/**
	 * @param string $input
	 *
	 * @return float
	 */
	public function calculate(string $input): float {
		$input = str_replace(' ', '', $input);
		$this->setInput($input);

		$divOutput = $this->fetchDivisions($input);

		$errors = $this->getErrors();
		if (!empty($errors)) {
			return 0;
		}

		$multOutput = $this->fetchMultiplications($divOutput);

		$sumOutput = $this->fetchSums($multOutput);

		$minustOutput = $this->fetchMinuses($sumOutput);

		echo "<pre>";
		var_dump($minustOutput);
		echo "</pre>";
		die;

		$finalOutput = floatval($minustOutput);

		$this->setOutput($finalOutput);
		return $finalOutput;
	}

	/**
	 * @return float
	 */
	public function fetchOutput(): float {
		return $this->getOutput();
	}

	/**
	 * @return float|int
	 */
	public function getOutput(): float|int {
		return $this->output;
	}

	/**
	 * @param float|int $output
	 *
	 * @return Calculator
	 */
	public function setOutput(float|int $output): Calculator {
		$this->output = $output;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getInput(): string {
		return $this->input;
	}

	/**
	 * @param string $input
	 *
	 * @return Calculator
	 */
	public function setInput(string $input): Calculator {
		$this->input = $input;
		return $this;
	}

	/**
	 * @param $error
	 */
	public function addError($error): void {
		$this->errors[] = $error;
	}

	/**
	 * @return array
	 */
	public function getErrors(): array {
		return $this->errors;
	}

	/**
	 * @param string $input
	 *
	 * @return string
	 */
	public function fetchDivisions(string $input): string {
		if (str_contains($input, '/')) {

			$arrParse = $this->parseExpression($input);

			echo "<pre>";
			print_r($arrParse);
			echo "</pre>";

			$parts = $arrParse['parts'];
			$posDiv = $arrParse['posSigns']['/'][0];

			$num1 = $parts[$posDiv - 1];
			$num2 = $parts[$posDiv + 1];

			if ($num2 == '-') {
				$num2 = "{$parts[$posDiv+1]}{$parts[$posDiv+2]}";
			}

			$expStr = "{$num1}/{$num2}";

			echo "<pre>";
			print_r($expStr);
			echo "</pre>";

			$divResult = $this->doDivision(floatval($num1), floatval($num2));

			echo "<pre>";
			print_r($divResult);
			echo "</pre>";

			$errors = $this->getErrors();

			if (empty($errors)) {
				$input = str_replace($expStr, $divResult, $input);

				$input = $this->fetchDivisions($input);
			}

			echo "<pre>";
			print_r($input);
			echo "</pre>";
		}

		return $input;
	}

	/**
	 * @param string $input
	 *
	 * @return string
	 */
	public function fetchMultiplications(string $input): string {
		if (str_contains($input, '*')) {

			$arrParse = $this->parseExpression($input);

			echo "<pre>";
			print_r($arrParse);
			echo "</pre>";

			$parts = $arrParse['parts'];
			$posMult = $arrParse['posSigns']['*'][0];

			$num1 = $parts[$posMult - 1];
			$num2 = $parts[$posMult + 1];

			if ($num2 == '-') {
				$num2 = "{$parts[$posMult+1]}{$parts[$posMult+2]}";
			}

			$expStr = "{$num1}*{$num2}";

			echo "<pre>";
			print_r($expStr);
			echo "</pre>";

			$multResult = $this->doMultiplication(floatval($num1), floatval($num2));

			echo "<pre>";
			print_r($multResult);
			echo "</pre>";

			$input = str_replace($expStr, $multResult, $input);

			echo "<pre>";
			print_r($input);
			echo "</pre>";

			$input = $this->fetchMultiplications($input);
		}

		return $input;
	}

	/**
	 * @param string $input
	 *
	 * @return string
	 */
	public function fetchSums(string $input): string {
		if (str_contains($input, '+')) {
			$arrParse = $this->parseExpression($input);

			echo "<pre>";
			print_r($arrParse);
			echo "</pre>";

			$parts = $arrParse['parts'];
			$posSum = $arrParse['posSigns']['+'][0];

			$num1 = $parts[$posSum - 1];
			if (isset($parts[$posSum - 2]) && $parts[$posSum - 2] == '-') {
				$num1 = "-{$num1}";
			}

			$num2 = $parts[$posSum + 1];
			if ($num2 == '-') {
				$num2 = "{$parts[$posSum+1]}{$parts[$posSum+2]}";
			}

			$expStr = "{$num1}+{$num2}";

			echo "<pre>";
			print_r($expStr);
			echo "</pre>";

			$sumResult = $this->doSum(floatval($num1), floatval($num2));

			echo "<pre>";
			print_r($sumResult);
			echo "</pre>";

			$input = str_replace($expStr, $sumResult, $input);

			echo "<pre>";
			print_r($input);
			echo "</pre>";

			$input = $this->fetchSums($input);
		}

		return $input;
	}

	/**
	 * @param string $input
	 *
	 * @return string
	 */
	public function fetchMinuses(string $input): string {
		if (str_contains($input, '-') && !is_numeric($input)) {
			$arrParse = $this->parseExpression($input);

			echo "<pre>";
			print_r($arrParse);
			echo "</pre>";

			$parts = $arrParse['parts'];
			$posMin = $arrParse['posSigns']['-'][0];

			if (str_starts_with($input, '-')) {
				$num1 = "{$parts[0]}{$parts[$posMin-1]}";
			} elseif ($parts[$posMin - 2] == '-') {
				$num1 = "{$parts[$posMin-2]}{$parts[$posMin-1]}";
			} else {
				$num1 = $parts[$posMin - 1];
			}
			$num2 = $parts[$posMin + 1];

			$expStr = "{$num1}-{$num2}";

			echo "<pre>";
			print_r($expStr);
			echo "</pre>";

			$minusResult = $this->doMinus(floatval($num1), floatval($num2));

			echo "<pre>";
			print_r($minusResult);
			echo "</pre>";

			$input = str_replace($expStr, $minusResult, $input);

			echo "<pre>";
			print_r($input);
			echo "</pre>";

			$input = $this->fetchMinuses($input);
		}

		return $input;
	}

	/**
	 * @param float $num1
	 * @param float $num2
	 *
	 * @return float
	 */
	public function doDivision(float $num1, float $num2): float {

		if ($num2 == 0) {
			$this->addError('Cannot do division by 0');
			return 0;
		}

		return $num1 / $num2;
	}

	/**
	 * @param float $num1
	 * @param float $num2
	 *
	 * @return float
	 */
	public function doMultiplication(float $num1, float $num2): float {
		return $num1 * $num2;
	}

	/**
	 * @param float $num1
	 * @param float $num2
	 *
	 * @return float
	 */
	public function doSum(float $num1, float $num2): float {
		return $num1 + $num2;
	}

	/**
	 * @param float $num1
	 * @param float $num2
	 *
	 * @return float
	 */
	public function doMinus(float $num1, float $num2) {
		return $num1 - $num2;
	}

	/**
	 * @param string $input
	 *
	 * @return array
	 */
	public function parseExpression(string $input): array {
		$expressionParts = preg_split('/([0-9]*\.?[0-9]+|\+|-|\*|\/)/', $input, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		$expressionParts = array_map('trim', $expressionParts);

		$posSigns = [];
		foreach ($expressionParts as $key => $item) {

			if ($item == '-' && ($key == 0 || !is_numeric($expressionParts[$key - 1]))) {
				continue;
			}

			switch ($item) {
				case'+':
				case'-':
				case '*':
				case'/':
					$posSigns[$item][] = $key;
					break;
			}
		}

		return ['parts' => $expressionParts, 'posSigns' => $posSigns];
	}

}