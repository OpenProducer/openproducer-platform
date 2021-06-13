(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[67],{

/***/ "./node_modules/codemirror/mode/mathematica/mathematica.js":
/*!*****************************************************************!*\
  !*** ./node_modules/codemirror/mode/mathematica/mathematica.js ***!
  \*****************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("// CodeMirror, copyright (c) by Marijn Haverbeke and others\n// Distributed under an MIT license: https://codemirror.net/LICENSE\n\n// Mathematica mode copyright (c) 2015 by Calin Barbat\n// Based on code by Patrick Scheibe (halirutan)\n// See: https://github.com/halirutan/Mathematica-Source-Highlighting/tree/master/src/lang-mma.js\n\n(function(mod) {\n  if (true) // CommonJS\n    mod(__webpack_require__(/*! ../../lib/codemirror */ \"./node_modules/codemirror/lib/codemirror.js\"));\n  else {}\n})(function(CodeMirror) {\n\"use strict\";\n\nCodeMirror.defineMode('mathematica', function(_config, _parserConfig) {\n\n  // used pattern building blocks\n  var Identifier = '[a-zA-Z\\\\$][a-zA-Z0-9\\\\$]*';\n  var pBase      = \"(?:\\\\d+)\";\n  var pFloat     = \"(?:\\\\.\\\\d+|\\\\d+\\\\.\\\\d*|\\\\d+)\";\n  var pFloatBase = \"(?:\\\\.\\\\w+|\\\\w+\\\\.\\\\w*|\\\\w+)\";\n  var pPrecision = \"(?:`(?:`?\"+pFloat+\")?)\";\n\n  // regular expressions\n  var reBaseForm        = new RegExp('(?:'+pBase+'(?:\\\\^\\\\^'+pFloatBase+pPrecision+'?(?:\\\\*\\\\^[+-]?\\\\d+)?))');\n  var reFloatForm       = new RegExp('(?:' + pFloat + pPrecision + '?(?:\\\\*\\\\^[+-]?\\\\d+)?)');\n  var reIdInContext     = new RegExp('(?:`?)(?:' + Identifier + ')(?:`(?:' + Identifier + '))*(?:`?)');\n\n  function tokenBase(stream, state) {\n    var ch;\n\n    // get next character\n    ch = stream.next();\n\n    // string\n    if (ch === '\"') {\n      state.tokenize = tokenString;\n      return state.tokenize(stream, state);\n    }\n\n    // comment\n    if (ch === '(') {\n      if (stream.eat('*')) {\n        state.commentLevel++;\n        state.tokenize = tokenComment;\n        return state.tokenize(stream, state);\n      }\n    }\n\n    // go back one character\n    stream.backUp(1);\n\n    // look for numbers\n    // Numbers in a baseform\n    if (stream.match(reBaseForm, true, false)) {\n      return 'number';\n    }\n\n    // Mathematica numbers. Floats (1.2, .2, 1.) can have optionally a precision (`float) or an accuracy definition\n    // (``float). Note: while 1.2` is possible 1.2`` is not. At the end an exponent (float*^+12) can follow.\n    if (stream.match(reFloatForm, true, false)) {\n      return 'number';\n    }\n\n    /* In[23] and Out[34] */\n    if (stream.match(/(?:In|Out)\\[[0-9]*\\]/, true, false)) {\n      return 'atom';\n    }\n\n    // usage\n    if (stream.match(/([a-zA-Z\\$][a-zA-Z0-9\\$]*(?:`[a-zA-Z0-9\\$]+)*::usage)/, true, false)) {\n      return 'meta';\n    }\n\n    // message\n    if (stream.match(/([a-zA-Z\\$][a-zA-Z0-9\\$]*(?:`[a-zA-Z0-9\\$]+)*::[a-zA-Z\\$][a-zA-Z0-9\\$]*):?/, true, false)) {\n      return 'string-2';\n    }\n\n    // this makes a look-ahead match for something like variable:{_Integer}\n    // the match is then forwarded to the mma-patterns tokenizer.\n    if (stream.match(/([a-zA-Z\\$][a-zA-Z0-9\\$]*\\s*:)(?:(?:[a-zA-Z\\$][a-zA-Z0-9\\$]*)|(?:[^:=>~@\\^\\&\\*\\)\\[\\]'\\?,\\|])).*/, true, false)) {\n      return 'variable-2';\n    }\n\n    // catch variables which are used together with Blank (_), BlankSequence (__) or BlankNullSequence (___)\n    // Cannot start with a number, but can have numbers at any other position. Examples\n    // blub__Integer, a1_, b34_Integer32\n    if (stream.match(/[a-zA-Z\\$][a-zA-Z0-9\\$]*_+[a-zA-Z\\$][a-zA-Z0-9\\$]*/, true, false)) {\n      return 'variable-2';\n    }\n    if (stream.match(/[a-zA-Z\\$][a-zA-Z0-9\\$]*_+/, true, false)) {\n      return 'variable-2';\n    }\n    if (stream.match(/_+[a-zA-Z\\$][a-zA-Z0-9\\$]*/, true, false)) {\n      return 'variable-2';\n    }\n\n    // Named characters in Mathematica, like \\[Gamma].\n    if (stream.match(/\\\\\\[[a-zA-Z\\$][a-zA-Z0-9\\$]*\\]/, true, false)) {\n      return 'variable-3';\n    }\n\n    // Match all braces separately\n    if (stream.match(/(?:\\[|\\]|{|}|\\(|\\))/, true, false)) {\n      return 'bracket';\n    }\n\n    // Catch Slots (#, ##, #3, ##9 and the V10 named slots #name). I have never seen someone using more than one digit after #, so we match\n    // only one.\n    if (stream.match(/(?:#[a-zA-Z\\$][a-zA-Z0-9\\$]*|#+[0-9]?)/, true, false)) {\n      return 'variable-2';\n    }\n\n    // Literals like variables, keywords, functions\n    if (stream.match(reIdInContext, true, false)) {\n      return 'keyword';\n    }\n\n    // operators. Note that operators like @@ or /; are matched separately for each symbol.\n    if (stream.match(/(?:\\\\|\\+|\\-|\\*|\\/|,|;|\\.|:|@|~|=|>|<|&|\\||_|`|'|\\^|\\?|!|%)/, true, false)) {\n      return 'operator';\n    }\n\n    // everything else is an error\n    stream.next(); // advance the stream.\n    return 'error';\n  }\n\n  function tokenString(stream, state) {\n    var next, end = false, escaped = false;\n    while ((next = stream.next()) != null) {\n      if (next === '\"' && !escaped) {\n        end = true;\n        break;\n      }\n      escaped = !escaped && next === '\\\\';\n    }\n    if (end && !escaped) {\n      state.tokenize = tokenBase;\n    }\n    return 'string';\n  };\n\n  function tokenComment(stream, state) {\n    var prev, next;\n    while(state.commentLevel > 0 && (next = stream.next()) != null) {\n      if (prev === '(' && next === '*') state.commentLevel++;\n      if (prev === '*' && next === ')') state.commentLevel--;\n      prev = next;\n    }\n    if (state.commentLevel <= 0) {\n      state.tokenize = tokenBase;\n    }\n    return 'comment';\n  }\n\n  return {\n    startState: function() {return {tokenize: tokenBase, commentLevel: 0};},\n    token: function(stream, state) {\n      if (stream.eatSpace()) return null;\n      return state.tokenize(stream, state);\n    },\n    blockCommentStart: \"(*\",\n    blockCommentEnd: \"*)\"\n  };\n});\n\nCodeMirror.defineMIME('text/x-mathematica', {\n  name: 'mathematica'\n});\n\n});\n\n\n//# sourceURL=webpack:///./node_modules/codemirror/mode/mathematica/mathematica.js?");

/***/ })

}]);