const calcInput = document.getElementById('calcInput');
const calcResult = document.getElementById('calcResult');
const clearButton = document.getElementById('clear');
const calcButtons = document.querySelectorAll('.calcButton');
const operators = document.querySelectorAll('.operator');
let lastKey = '';


calcButtons.forEach(function (calcButton, index) {
    calcButton.addEventListener('click', e => {
        let buttonValue = e.target.value;
        addChar(calcInput, buttonValue);
    })
});

operators.forEach(function (operator, index) {
    operator.addEventListener('click', e => {
        let operatorValue = e.target.value;

        addChar(calcInput, operatorValue);
    });
});

clearButton.addEventListener('click', function () {
    clear();
})

/**
 *
 * @param input
 * @param character
 * @returns {boolean}
 */
function addChar(input, character) {
    let isOperator = charIsOperator(character);
    let isNumber = charIsNumber(character);

    if ((lastKey === '' || lastKey == '0') && isOperator && character !== '-') {
        return false;
    } else if (isOperator && lastKey === '-') {
        return false;
    } else if (isOperator && charIsOperator(lastKey) && character.match(/^[\/*+]$/) != null) {
        return false;
    } else if (lastKey === '.' && character === '.') {
        return false;
    } else if (isNumber && (input.value === '' || charIsOperator(lastKey)) && character === '.') {
        character = '0.';
    }

    if (input.value == null || input.value == "0" || calcResult.value !== '') {
        clear();
        input.value = character;
    } else {
        input.value += character;
    }
    lastKey = character;
}

/**
 * @param char
 * @returns {boolean}
 */
function charIsOperator(char) {
    return char.match(/^[\/*\-+]$/) != null;
}

/**
 * @param char
 * @returns {boolean}
 */
function charIsNumber(char) {
    return char.match(/^[\d.]$/) != null;
}

/**
 *
 */
function clear() {
    calcInput.value = '';
    calcResult.value = '';
    lastKey = '';
}