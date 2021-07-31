<?php

namespace App\Service;

/**
 * Class Calculator
 *
 * @package App\Service
 */
class Calculator
{
    /**
     * @var string
     */
    public string $input = "";

    /**
     * @var array
     */
    public array $errors = [];

    /**
     * @param string $input
     *
     * @return float
     */
    public function calculate(string $input): float
    {
        //cleanup of input to remove double operators and transform -- into +
        $input = str_replace([' ', '--'], ['', '+'], $input);
        $input = preg_replace('/\/+/', '/', $input);
        $input = preg_replace('/\*+/', '*', $input);
        $input = preg_replace('/\-+/', '-', $input);
        $input = preg_replace('/\++/', '+', $input);
        $input = preg_replace('/\.+/', '.', $input);

        //cleanup to remove last char is not a numeric value
        $lastChar = substr($input, -1);
        if (!is_numeric($lastChar)) {
            $input = substr($input, 0, strlen($input) - 1);
        }

        //stores the cleaned input to display later on
        $this->setInput($input);

        //starts operation cycle, division->multiplications->additions->rests
        $divOutput = $this->fetchDivisions($input);

        //if errors occur we catch them and force to return 0, example if division by 0 is made
        $errors = $this->getErrors();
        if (!empty($errors)) {
            return 0;
        }

        $multOutput = $this->fetchMultiplications($divOutput);
        $sumOutput = $this->fetchSums($multOutput);
        $minustOutput = $this->fetchMinuses($sumOutput);

        //transforms the final value into a float
        $finalOutput = floatval($minustOutput);

        return $finalOutput;
    }

    /**
     * @return string
     */
    public function getInput(): string
    {
        return $this->input;
    }

    /**
     * @param string $input
     *
     * @return Calculator
     */
    public function setInput(string $input): Calculator
    {
        $this->input = $input;
        return $this;
    }

    /**
     * @param $error
     */
    public function addError($error): void
    {
        $this->errors[] = $error;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param string $input
     *
     * @return string
     */
    public function fetchDivisions(string $input): string
    {
        if (str_contains($input, '/')) {
            $arrParse = $this->parseExpression($input);

            $parts = $arrParse['parts'];
            $posDiv = $arrParse['posSigns']['/'][0];

            $num1 = $parts[$posDiv - 1];
            $num2 = $parts[$posDiv + 1];

            unset($parts[$posDiv]);
            unset($parts[$posDiv - 1]);
            unset($parts[$posDiv + 1]);
            //we remove used items from the splitted expression

            if ($num2 == '-') {
                $num2 = "{$parts[$posDiv+1]}{$parts[$posDiv+2]}";
                unset($parts[$posDiv + 2]);
            }

            $divResult = $this->doDivision(floatval($num1), floatval($num2));
            $parts[$posDiv] = $divResult;
            ksort($parts);
            //we reform the splitted expression with the removed values replaced by the result of the operation

            $errors = $this->getErrors();

            if (empty($errors)) {
                $input = implode('', $parts);

                $input = $this->fetchDivisions($input);
            }
        }

        return $input;
    }

    /**
     * @param string $input
     *
     * @return string
     */
    public function fetchMultiplications(string $input): string
    {
        if (str_contains($input, '*')) {

            $arrParse = $this->parseExpression($input);

            $parts = $arrParse['parts'];
            $posMult = $arrParse['posSigns']['*'][0];

            $num1 = $parts[$posMult - 1];
            $num2 = $parts[$posMult + 1];

            unset($parts[$posMult]);
            unset($parts[$posMult - 1]);
            unset($parts[$posMult + 1]);

            if ($num2 == '-') {
                $num2 = "{$parts[$posMult+1]}{$parts[$posMult+2]}";
                unset($parts[$posMult + 2]);
            }

            $multResult = $this->doMultiplication(floatval($num1), floatval($num2));

            $parts[$posMult] = $multResult;
            ksort($parts);
            $input = implode('', $parts);

            $input = $this->fetchMultiplications($input);
        }

        return $input;
    }

    /**
     * @param string $input
     *
     * @return string
     */
    public function fetchSums(string $input): string
    {
        if (str_contains($input, '+')) {
            $arrParse = $this->parseExpression($input);

            $parts = $arrParse['parts'];
            $posSum = $arrParse['posSigns']['+'][0];

            unset($parts[$posSum]);

            $num1 = $parts[$posSum - 1];
            unset($parts[$posSum - 1]);
            if (isset($parts[$posSum - 2]) && $parts[$posSum - 2] == '-') {
                $num1 = "-{$num1}";
                unset($parts[$posSum - 2]);
            }

            $num2 = $parts[$posSum + 1];
            unset($parts[$posSum + 1]);
            if ($num2 == '-') {
                $num2 = "{$parts[$posSum+1]}{$parts[$posSum+2]}";
                unset($parts[$posSum + 2]);
            }

            $sumResult = $this->doSum(floatval($num1), floatval($num2));

            $parts[$posSum] = $sumResult;
            ksort($parts);
            $input = implode('', $parts);

            $input = $this->fetchSums($input);
        }

        return $input;
    }

    /**
     * @param string $input
     *
     * @return string
     */
    public function fetchMinuses(string $input): string
    {
        if (str_contains($input, '-') && !is_numeric($input)) {
            $arrParse = $this->parseExpression($input);

            $parts = $arrParse['parts'];
            $posMin = $arrParse['posSigns']['-'][0];

            unset($parts[$posMin]);

            if (str_starts_with($input, '-')) {
                $num1 = "{$parts[0]}{$parts[$posMin-1]}";
                unset($parts[0]);
                unset($parts[$posMin - 1]);
            } elseif (isset($parts[$posMin - 2]) && $parts[$posMin - 2] == '-') {
                $num1 = "{$parts[$posMin-2]}{$parts[$posMin-1]}";
                unset($parts[$posMin - 1]);
                unset($parts[$posMin - 2]);
            } else {
                $num1 = $parts[$posMin - 1];
                unset($parts[$posMin - 1]);
            }
            $num2 = $parts[$posMin + 1];
            unset($parts[$posMin + 1]);

            $minusResult = $this->doMinus(floatval($num1), floatval($num2));

            $parts[$posMin] = $minusResult;
            ksort($parts);

            $input = implode('', $parts);

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
    public function doDivision(float $num1, float $num2): float
    {
        //if we try to divide by 0 we return 0 plus add an error
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
    public function doMultiplication(float $num1, float $num2): float
    {
        return $num1 * $num2;
    }

    /**
     * @param float $num1
     * @param float $num2
     *
     * @return float
     */
    public function doSum(float $num1, float $num2): float
    {
        return $num1 + $num2;
    }

    /**
     * @param float $num1
     * @param float $num2
     *
     * @return float
     */
    public function doMinus(float $num1, float $num2)
    {
        return $num1 - $num2;
    }

    /**
     * @param string $input
     *
     * @return array
     */
    public function parseExpression(string $input): array
    {
        //splits expression into numbers and operators
        $expressionParts = preg_split('/([0-9]*\.?[0-9]+|\+|-|\*|\/)/', $input, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $expressionParts = array_map('trim', $expressionParts);

        $posSigns = [];

        //loop through splitted expression items to fetch the positions of each of the operator
        foreach ($expressionParts as $key => $item) {
            //if the 1st item is a - we skip it as it belongs to the number that comes after it
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