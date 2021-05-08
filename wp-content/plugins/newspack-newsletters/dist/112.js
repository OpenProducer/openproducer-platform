(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[112],{

/***/ "./node_modules/codemirror/mode/velocity/velocity.js":
/*!***********************************************************!*\
  !*** ./node_modules/codemirror/mode/velocity/velocity.js ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("// CodeMirror, copyright (c) by Marijn Haverbeke and others\n// Distributed under an MIT license: https://codemirror.net/LICENSE\n\n(function(mod) {\n  if (true) // CommonJS\n    mod(__webpack_require__(/*! ../../lib/codemirror */ \"./node_modules/codemirror/lib/codemirror.js\"));\n  else {}\n})(function(CodeMirror) {\n\"use strict\";\n\nCodeMirror.defineMode(\"velocity\", function() {\n    function parseWords(str) {\n        var obj = {}, words = str.split(\" \");\n        for (var i = 0; i < words.length; ++i) obj[words[i]] = true;\n        return obj;\n    }\n\n    var keywords = parseWords(\"#end #else #break #stop #[[ #]] \" +\n                              \"#{end} #{else} #{break} #{stop}\");\n    var functions = parseWords(\"#if #elseif #foreach #set #include #parse #macro #define #evaluate \" +\n                               \"#{if} #{elseif} #{foreach} #{set} #{include} #{parse} #{macro} #{define} #{evaluate}\");\n    var specials = parseWords(\"$foreach.count $foreach.hasNext $foreach.first $foreach.last $foreach.topmost $foreach.parent.count $foreach.parent.hasNext $foreach.parent.first $foreach.parent.last $foreach.parent $velocityCount $!bodyContent $bodyContent\");\n    var isOperatorChar = /[+\\-*&%=<>!?:\\/|]/;\n\n    function chain(stream, state, f) {\n        state.tokenize = f;\n        return f(stream, state);\n    }\n    function tokenBase(stream, state) {\n        var beforeParams = state.beforeParams;\n        state.beforeParams = false;\n        var ch = stream.next();\n        // start of unparsed string?\n        if ((ch == \"'\") && !state.inString && state.inParams) {\n            state.lastTokenWasBuiltin = false;\n            return chain(stream, state, tokenString(ch));\n        }\n        // start of parsed string?\n        else if ((ch == '\"')) {\n            state.lastTokenWasBuiltin = false;\n            if (state.inString) {\n                state.inString = false;\n                return \"string\";\n            }\n            else if (state.inParams)\n                return chain(stream, state, tokenString(ch));\n        }\n        // is it one of the special signs []{}().,;? Separator?\n        else if (/[\\[\\]{}\\(\\),;\\.]/.test(ch)) {\n            if (ch == \"(\" && beforeParams)\n                state.inParams = true;\n            else if (ch == \")\") {\n                state.inParams = false;\n                state.lastTokenWasBuiltin = true;\n            }\n            return null;\n        }\n        // start of a number value?\n        else if (/\\d/.test(ch)) {\n            state.lastTokenWasBuiltin = false;\n            stream.eatWhile(/[\\w\\.]/);\n            return \"number\";\n        }\n        // multi line comment?\n        else if (ch == \"#\" && stream.eat(\"*\")) {\n            state.lastTokenWasBuiltin = false;\n            return chain(stream, state, tokenComment);\n        }\n        // unparsed content?\n        else if (ch == \"#\" && stream.match(/ *\\[ *\\[/)) {\n            state.lastTokenWasBuiltin = false;\n            return chain(stream, state, tokenUnparsed);\n        }\n        // single line comment?\n        else if (ch == \"#\" && stream.eat(\"#\")) {\n            state.lastTokenWasBuiltin = false;\n            stream.skipToEnd();\n            return \"comment\";\n        }\n        // variable?\n        else if (ch == \"$\") {\n            stream.eatWhile(/[\\w\\d\\$_\\.{}-]/);\n            // is it one of the specials?\n            if (specials && specials.propertyIsEnumerable(stream.current())) {\n                return \"keyword\";\n            }\n            else {\n                state.lastTokenWasBuiltin = true;\n                state.beforeParams = true;\n                return \"builtin\";\n            }\n        }\n        // is it a operator?\n        else if (isOperatorChar.test(ch)) {\n            state.lastTokenWasBuiltin = false;\n            stream.eatWhile(isOperatorChar);\n            return \"operator\";\n        }\n        else {\n            // get the whole word\n            stream.eatWhile(/[\\w\\$_{}@]/);\n            var word = stream.current();\n            // is it one of the listed keywords?\n            if (keywords && keywords.propertyIsEnumerable(word))\n                return \"keyword\";\n            // is it one of the listed functions?\n            if (functions && functions.propertyIsEnumerable(word) ||\n                    (stream.current().match(/^#@?[a-z0-9_]+ *$/i) && stream.peek()==\"(\") &&\n                     !(functions && functions.propertyIsEnumerable(word.toLowerCase()))) {\n                state.beforeParams = true;\n                state.lastTokenWasBuiltin = false;\n                return \"keyword\";\n            }\n            if (state.inString) {\n                state.lastTokenWasBuiltin = false;\n                return \"string\";\n            }\n            if (stream.pos > word.length && stream.string.charAt(stream.pos-word.length-1)==\".\" && state.lastTokenWasBuiltin)\n                return \"builtin\";\n            // default: just a \"word\"\n            state.lastTokenWasBuiltin = false;\n            return null;\n        }\n    }\n\n    function tokenString(quote) {\n        return function(stream, state) {\n            var escaped = false, next, end = false;\n            while ((next = stream.next()) != null) {\n                if ((next == quote) && !escaped) {\n                    end = true;\n                    break;\n                }\n                if (quote=='\"' && stream.peek() == '$' && !escaped) {\n                    state.inString = true;\n                    end = true;\n                    break;\n                }\n                escaped = !escaped && next == \"\\\\\";\n            }\n            if (end) state.tokenize = tokenBase;\n            return \"string\";\n        };\n    }\n\n    function tokenComment(stream, state) {\n        var maybeEnd = false, ch;\n        while (ch = stream.next()) {\n            if (ch == \"#\" && maybeEnd) {\n                state.tokenize = tokenBase;\n                break;\n            }\n            maybeEnd = (ch == \"*\");\n        }\n        return \"comment\";\n    }\n\n    function tokenUnparsed(stream, state) {\n        var maybeEnd = 0, ch;\n        while (ch = stream.next()) {\n            if (ch == \"#\" && maybeEnd == 2) {\n                state.tokenize = tokenBase;\n                break;\n            }\n            if (ch == \"]\")\n                maybeEnd++;\n            else if (ch != \" \")\n                maybeEnd = 0;\n        }\n        return \"meta\";\n    }\n    // Interface\n\n    return {\n        startState: function() {\n            return {\n                tokenize: tokenBase,\n                beforeParams: false,\n                inParams: false,\n                inString: false,\n                lastTokenWasBuiltin: false\n            };\n        },\n\n        token: function(stream, state) {\n            if (stream.eatSpace()) return null;\n            return state.tokenize(stream, state);\n        },\n        blockCommentStart: \"#*\",\n        blockCommentEnd: \"*#\",\n        lineComment: \"##\",\n        fold: \"velocity\"\n    };\n});\n\nCodeMirror.defineMIME(\"text/velocity\", \"velocity\");\n\n});\n\n\n//# sourceURL=webpack:///./node_modules/codemirror/mode/velocity/velocity.js?");

/***/ })

}]);