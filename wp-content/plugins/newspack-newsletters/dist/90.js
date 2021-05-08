(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[90],{

/***/ "./node_modules/codemirror/mode/scheme/scheme.js":
/*!*******************************************************!*\
  !*** ./node_modules/codemirror/mode/scheme/scheme.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("// CodeMirror, copyright (c) by Marijn Haverbeke and others\n// Distributed under an MIT license: https://codemirror.net/LICENSE\n\n/**\n * Author: Koh Zi Han, based on implementation by Koh Zi Chun\n */\n\n(function(mod) {\n  if (true) // CommonJS\n    mod(__webpack_require__(/*! ../../lib/codemirror */ \"./node_modules/codemirror/lib/codemirror.js\"));\n  else {}\n})(function(CodeMirror) {\n\"use strict\";\n\nCodeMirror.defineMode(\"scheme\", function () {\n    var BUILTIN = \"builtin\", COMMENT = \"comment\", STRING = \"string\",\n        ATOM = \"atom\", NUMBER = \"number\", BRACKET = \"bracket\";\n    var INDENT_WORD_SKIP = 2;\n\n    function makeKeywords(str) {\n        var obj = {}, words = str.split(\" \");\n        for (var i = 0; i < words.length; ++i) obj[words[i]] = true;\n        return obj;\n    }\n\n    var keywords = makeKeywords(\"λ case-lambda call/cc class cond-expand define-class define-values exit-handler field import inherit init-field interface let*-values let-values let/ec mixin opt-lambda override protect provide public rename require require-for-syntax syntax syntax-case syntax-error unit/sig unless when with-syntax and begin call-with-current-continuation call-with-input-file call-with-output-file case cond define define-syntax define-macro defmacro delay do dynamic-wind else for-each if lambda let let* let-syntax letrec letrec-syntax map or syntax-rules abs acos angle append apply asin assoc assq assv atan boolean? caar cadr call-with-input-file call-with-output-file call-with-values car cdddar cddddr cdr ceiling char->integer char-alphabetic? char-ci<=? char-ci<? char-ci=? char-ci>=? char-ci>? char-downcase char-lower-case? char-numeric? char-ready? char-upcase char-upper-case? char-whitespace? char<=? char<? char=? char>=? char>? char? close-input-port close-output-port complex? cons cos current-input-port current-output-port denominator display eof-object? eq? equal? eqv? eval even? exact->inexact exact? exp expt #f floor force gcd imag-part inexact->exact inexact? input-port? integer->char integer? interaction-environment lcm length list list->string list->vector list-ref list-tail list? load log magnitude make-polar make-rectangular make-string make-vector max member memq memv min modulo negative? newline not null-environment null? number->string number? numerator odd? open-input-file open-output-file output-port? pair? peek-char port? positive? procedure? quasiquote quote quotient rational? rationalize read read-char real-part real? remainder reverse round scheme-report-environment set! set-car! set-cdr! sin sqrt string string->list string->number string->symbol string-append string-ci<=? string-ci<? string-ci=? string-ci>=? string-ci>? string-copy string-fill! string-length string-ref string-set! string<=? string<? string=? string>=? string>? string? substring symbol->string symbol? #t tan transcript-off transcript-on truncate values vector vector->list vector-fill! vector-length vector-ref vector-set! with-input-from-file with-output-to-file write write-char zero?\");\n    var indentKeys = makeKeywords(\"define let letrec let* lambda define-macro defmacro let-syntax letrec-syntax let-values let*-values define-syntax syntax-rules define-values when unless\");\n\n    function stateStack(indent, type, prev) { // represents a state stack object\n        this.indent = indent;\n        this.type = type;\n        this.prev = prev;\n    }\n\n    function pushStack(state, indent, type) {\n        state.indentStack = new stateStack(indent, type, state.indentStack);\n    }\n\n    function popStack(state) {\n        state.indentStack = state.indentStack.prev;\n    }\n\n    var binaryMatcher = new RegExp(/^(?:[-+]i|[-+][01]+#*(?:\\/[01]+#*)?i|[-+]?[01]+#*(?:\\/[01]+#*)?@[-+]?[01]+#*(?:\\/[01]+#*)?|[-+]?[01]+#*(?:\\/[01]+#*)?[-+](?:[01]+#*(?:\\/[01]+#*)?)?i|[-+]?[01]+#*(?:\\/[01]+#*)?)(?=[()\\s;\"]|$)/i);\n    var octalMatcher = new RegExp(/^(?:[-+]i|[-+][0-7]+#*(?:\\/[0-7]+#*)?i|[-+]?[0-7]+#*(?:\\/[0-7]+#*)?@[-+]?[0-7]+#*(?:\\/[0-7]+#*)?|[-+]?[0-7]+#*(?:\\/[0-7]+#*)?[-+](?:[0-7]+#*(?:\\/[0-7]+#*)?)?i|[-+]?[0-7]+#*(?:\\/[0-7]+#*)?)(?=[()\\s;\"]|$)/i);\n    var hexMatcher = new RegExp(/^(?:[-+]i|[-+][\\da-f]+#*(?:\\/[\\da-f]+#*)?i|[-+]?[\\da-f]+#*(?:\\/[\\da-f]+#*)?@[-+]?[\\da-f]+#*(?:\\/[\\da-f]+#*)?|[-+]?[\\da-f]+#*(?:\\/[\\da-f]+#*)?[-+](?:[\\da-f]+#*(?:\\/[\\da-f]+#*)?)?i|[-+]?[\\da-f]+#*(?:\\/[\\da-f]+#*)?)(?=[()\\s;\"]|$)/i);\n    var decimalMatcher = new RegExp(/^(?:[-+]i|[-+](?:(?:(?:\\d+#+\\.?#*|\\d+\\.\\d*#*|\\.\\d+#*|\\d+)(?:[esfdl][-+]?\\d+)?)|\\d+#*\\/\\d+#*)i|[-+]?(?:(?:(?:\\d+#+\\.?#*|\\d+\\.\\d*#*|\\.\\d+#*|\\d+)(?:[esfdl][-+]?\\d+)?)|\\d+#*\\/\\d+#*)@[-+]?(?:(?:(?:\\d+#+\\.?#*|\\d+\\.\\d*#*|\\.\\d+#*|\\d+)(?:[esfdl][-+]?\\d+)?)|\\d+#*\\/\\d+#*)|[-+]?(?:(?:(?:\\d+#+\\.?#*|\\d+\\.\\d*#*|\\.\\d+#*|\\d+)(?:[esfdl][-+]?\\d+)?)|\\d+#*\\/\\d+#*)[-+](?:(?:(?:\\d+#+\\.?#*|\\d+\\.\\d*#*|\\.\\d+#*|\\d+)(?:[esfdl][-+]?\\d+)?)|\\d+#*\\/\\d+#*)?i|(?:(?:(?:\\d+#+\\.?#*|\\d+\\.\\d*#*|\\.\\d+#*|\\d+)(?:[esfdl][-+]?\\d+)?)|\\d+#*\\/\\d+#*))(?=[()\\s;\"]|$)/i);\n\n    function isBinaryNumber (stream) {\n        return stream.match(binaryMatcher);\n    }\n\n    function isOctalNumber (stream) {\n        return stream.match(octalMatcher);\n    }\n\n    function isDecimalNumber (stream, backup) {\n        if (backup === true) {\n            stream.backUp(1);\n        }\n        return stream.match(decimalMatcher);\n    }\n\n    function isHexNumber (stream) {\n        return stream.match(hexMatcher);\n    }\n\n    return {\n        startState: function () {\n            return {\n                indentStack: null,\n                indentation: 0,\n                mode: false,\n                sExprComment: false,\n                sExprQuote: false\n            };\n        },\n\n        token: function (stream, state) {\n            if (state.indentStack == null && stream.sol()) {\n                // update indentation, but only if indentStack is empty\n                state.indentation = stream.indentation();\n            }\n\n            // skip spaces\n            if (stream.eatSpace()) {\n                return null;\n            }\n            var returnType = null;\n\n            switch(state.mode){\n                case \"string\": // multi-line string parsing mode\n                    var next, escaped = false;\n                    while ((next = stream.next()) != null) {\n                        if (next == \"\\\"\" && !escaped) {\n\n                            state.mode = false;\n                            break;\n                        }\n                        escaped = !escaped && next == \"\\\\\";\n                    }\n                    returnType = STRING; // continue on in scheme-string mode\n                    break;\n                case \"comment\": // comment parsing mode\n                    var next, maybeEnd = false;\n                    while ((next = stream.next()) != null) {\n                        if (next == \"#\" && maybeEnd) {\n\n                            state.mode = false;\n                            break;\n                        }\n                        maybeEnd = (next == \"|\");\n                    }\n                    returnType = COMMENT;\n                    break;\n                case \"s-expr-comment\": // s-expr commenting mode\n                    state.mode = false;\n                    if(stream.peek() == \"(\" || stream.peek() == \"[\"){\n                        // actually start scheme s-expr commenting mode\n                        state.sExprComment = 0;\n                    }else{\n                        // if not we just comment the entire of the next token\n                        stream.eatWhile(/[^\\s\\(\\)\\[\\]]/); // eat symbol atom\n                        returnType = COMMENT;\n                        break;\n                    }\n                default: // default parsing mode\n                    var ch = stream.next();\n\n                    if (ch == \"\\\"\") {\n                        state.mode = \"string\";\n                        returnType = STRING;\n\n                    } else if (ch == \"'\") {\n                        if (stream.peek() == \"(\" || stream.peek() == \"[\"){\n                            if (typeof state.sExprQuote != \"number\") {\n                                state.sExprQuote = 0;\n                            } // else already in a quoted expression\n                            returnType = ATOM;\n                        } else {\n                            stream.eatWhile(/[\\w_\\-!$%&*+\\.\\/:<=>?@\\^~]/);\n                            returnType = ATOM;\n                        }\n                    } else if (ch == '#') {\n                        if (stream.eat(\"|\")) {                    // Multi-line comment\n                            state.mode = \"comment\"; // toggle to comment mode\n                            returnType = COMMENT;\n                        } else if (stream.eat(/[tf]/i)) {            // #t/#f (atom)\n                            returnType = ATOM;\n                        } else if (stream.eat(';')) {                // S-Expr comment\n                            state.mode = \"s-expr-comment\";\n                            returnType = COMMENT;\n                        } else {\n                            var numTest = null, hasExactness = false, hasRadix = true;\n                            if (stream.eat(/[ei]/i)) {\n                                hasExactness = true;\n                            } else {\n                                stream.backUp(1);       // must be radix specifier\n                            }\n                            if (stream.match(/^#b/i)) {\n                                numTest = isBinaryNumber;\n                            } else if (stream.match(/^#o/i)) {\n                                numTest = isOctalNumber;\n                            } else if (stream.match(/^#x/i)) {\n                                numTest = isHexNumber;\n                            } else if (stream.match(/^#d/i)) {\n                                numTest = isDecimalNumber;\n                            } else if (stream.match(/^[-+0-9.]/, false)) {\n                                hasRadix = false;\n                                numTest = isDecimalNumber;\n                            // re-consume the initial # if all matches failed\n                            } else if (!hasExactness) {\n                                stream.eat('#');\n                            }\n                            if (numTest != null) {\n                                if (hasRadix && !hasExactness) {\n                                    // consume optional exactness after radix\n                                    stream.match(/^#[ei]/i);\n                                }\n                                if (numTest(stream))\n                                    returnType = NUMBER;\n                            }\n                        }\n                    } else if (/^[-+0-9.]/.test(ch) && isDecimalNumber(stream, true)) { // match non-prefixed number, must be decimal\n                        returnType = NUMBER;\n                    } else if (ch == \";\") { // comment\n                        stream.skipToEnd(); // rest of the line is a comment\n                        returnType = COMMENT;\n                    } else if (ch == \"(\" || ch == \"[\") {\n                      var keyWord = ''; var indentTemp = stream.column(), letter;\n                        /**\n                        Either\n                        (indent-word ..\n                        (non-indent-word ..\n                        (;something else, bracket, etc.\n                        */\n\n                        while ((letter = stream.eat(/[^\\s\\(\\[\\;\\)\\]]/)) != null) {\n                            keyWord += letter;\n                        }\n\n                        if (keyWord.length > 0 && indentKeys.propertyIsEnumerable(keyWord)) { // indent-word\n\n                            pushStack(state, indentTemp + INDENT_WORD_SKIP, ch);\n                        } else { // non-indent word\n                            // we continue eating the spaces\n                            stream.eatSpace();\n                            if (stream.eol() || stream.peek() == \";\") {\n                                // nothing significant after\n                                // we restart indentation 1 space after\n                                pushStack(state, indentTemp + 1, ch);\n                            } else {\n                                pushStack(state, indentTemp + stream.current().length, ch); // else we match\n                            }\n                        }\n                        stream.backUp(stream.current().length - 1); // undo all the eating\n\n                        if(typeof state.sExprComment == \"number\") state.sExprComment++;\n                        if(typeof state.sExprQuote == \"number\") state.sExprQuote++;\n\n                        returnType = BRACKET;\n                    } else if (ch == \")\" || ch == \"]\") {\n                        returnType = BRACKET;\n                        if (state.indentStack != null && state.indentStack.type == (ch == \")\" ? \"(\" : \"[\")) {\n                            popStack(state);\n\n                            if(typeof state.sExprComment == \"number\"){\n                                if(--state.sExprComment == 0){\n                                    returnType = COMMENT; // final closing bracket\n                                    state.sExprComment = false; // turn off s-expr commenting mode\n                                }\n                            }\n                            if(typeof state.sExprQuote == \"number\"){\n                                if(--state.sExprQuote == 0){\n                                    returnType = ATOM; // final closing bracket\n                                    state.sExprQuote = false; // turn off s-expr quote mode\n                                }\n                            }\n                        }\n                    } else {\n                        stream.eatWhile(/[\\w_\\-!$%&*+\\.\\/:<=>?@\\^~]/);\n\n                        if (keywords && keywords.propertyIsEnumerable(stream.current())) {\n                            returnType = BUILTIN;\n                        } else returnType = \"variable\";\n                    }\n            }\n            return (typeof state.sExprComment == \"number\") ? COMMENT : ((typeof state.sExprQuote == \"number\") ? ATOM : returnType);\n        },\n\n        indent: function (state) {\n            if (state.indentStack == null) return state.indentation;\n            return state.indentStack.indent;\n        },\n\n        closeBrackets: {pairs: \"()[]{}\\\"\\\"\"},\n        lineComment: \";;\"\n    };\n});\n\nCodeMirror.defineMIME(\"text/x-scheme\", \"scheme\");\n\n});\n\n\n//# sourceURL=webpack:///./node_modules/codemirror/mode/scheme/scheme.js?");

/***/ })

}]);