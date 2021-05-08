(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[115],{

/***/ "./node_modules/codemirror/mode/webidl/webidl.js":
/*!*******************************************************!*\
  !*** ./node_modules/codemirror/mode/webidl/webidl.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("// CodeMirror, copyright (c) by Marijn Haverbeke and others\n// Distributed under an MIT license: https://codemirror.net/LICENSE\n\n(function(mod) {\n  if (true) // CommonJS\n    mod(__webpack_require__(/*! ../../lib/codemirror */ \"./node_modules/codemirror/lib/codemirror.js\"));\n  else {}\n})(function(CodeMirror) {\n\"use strict\";\n\nfunction wordRegexp(words) {\n  return new RegExp(\"^((\" + words.join(\")|(\") + \"))\\\\b\");\n};\n\nvar builtinArray = [\n  \"Clamp\",\n  \"Constructor\",\n  \"EnforceRange\",\n  \"Exposed\",\n  \"ImplicitThis\",\n  \"Global\", \"PrimaryGlobal\",\n  \"LegacyArrayClass\",\n  \"LegacyUnenumerableNamedProperties\",\n  \"LenientThis\",\n  \"NamedConstructor\",\n  \"NewObject\",\n  \"NoInterfaceObject\",\n  \"OverrideBuiltins\",\n  \"PutForwards\",\n  \"Replaceable\",\n  \"SameObject\",\n  \"TreatNonObjectAsNull\",\n  \"TreatNullAs\",\n    \"EmptyString\",\n  \"Unforgeable\",\n  \"Unscopeable\"\n];\nvar builtins = wordRegexp(builtinArray);\n\nvar typeArray = [\n  \"unsigned\", \"short\", \"long\",                  // UnsignedIntegerType\n  \"unrestricted\", \"float\", \"double\",            // UnrestrictedFloatType\n  \"boolean\", \"byte\", \"octet\",                   // Rest of PrimitiveType\n  \"Promise\",                                    // PromiseType\n  \"ArrayBuffer\", \"DataView\", \"Int8Array\", \"Int16Array\", \"Int32Array\",\n  \"Uint8Array\", \"Uint16Array\", \"Uint32Array\", \"Uint8ClampedArray\",\n  \"Float32Array\", \"Float64Array\",               // BufferRelatedType\n  \"ByteString\", \"DOMString\", \"USVString\", \"sequence\", \"object\", \"RegExp\",\n  \"Error\", \"DOMException\", \"FrozenArray\",       // Rest of NonAnyType\n  \"any\",                                        // Rest of SingleType\n  \"void\"                                        // Rest of ReturnType\n];\nvar types = wordRegexp(typeArray);\n\nvar keywordArray = [\n  \"attribute\", \"callback\", \"const\", \"deleter\", \"dictionary\", \"enum\", \"getter\",\n  \"implements\", \"inherit\", \"interface\", \"iterable\", \"legacycaller\", \"maplike\",\n  \"partial\", \"required\", \"serializer\", \"setlike\", \"setter\", \"static\",\n  \"stringifier\", \"typedef\",                     // ArgumentNameKeyword except\n                                                // \"unrestricted\"\n  \"optional\", \"readonly\", \"or\"\n];\nvar keywords = wordRegexp(keywordArray);\n\nvar atomArray = [\n  \"true\", \"false\",                              // BooleanLiteral\n  \"Infinity\", \"NaN\",                            // FloatLiteral\n  \"null\"                                        // Rest of ConstValue\n];\nvar atoms = wordRegexp(atomArray);\n\nCodeMirror.registerHelper(\"hintWords\", \"webidl\",\n    builtinArray.concat(typeArray).concat(keywordArray).concat(atomArray));\n\nvar startDefArray = [\"callback\", \"dictionary\", \"enum\", \"interface\"];\nvar startDefs = wordRegexp(startDefArray);\n\nvar endDefArray = [\"typedef\"];\nvar endDefs = wordRegexp(endDefArray);\n\nvar singleOperators = /^[:<=>?]/;\nvar integers = /^-?([1-9][0-9]*|0[Xx][0-9A-Fa-f]+|0[0-7]*)/;\nvar floats = /^-?(([0-9]+\\.[0-9]*|[0-9]*\\.[0-9]+)([Ee][+-]?[0-9]+)?|[0-9]+[Ee][+-]?[0-9]+)/;\nvar identifiers = /^_?[A-Za-z][0-9A-Z_a-z-]*/;\nvar identifiersEnd = /^_?[A-Za-z][0-9A-Z_a-z-]*(?=\\s*;)/;\nvar strings = /^\"[^\"]*\"/;\nvar multilineComments = /^\\/\\*.*?\\*\\//;\nvar multilineCommentsStart = /^\\/\\*.*/;\nvar multilineCommentsEnd = /^.*?\\*\\//;\n\nfunction readToken(stream, state) {\n  // whitespace\n  if (stream.eatSpace()) return null;\n\n  // comment\n  if (state.inComment) {\n    if (stream.match(multilineCommentsEnd)) {\n      state.inComment = false;\n      return \"comment\";\n    }\n    stream.skipToEnd();\n    return \"comment\";\n  }\n  if (stream.match(\"//\")) {\n    stream.skipToEnd();\n    return \"comment\";\n  }\n  if (stream.match(multilineComments)) return \"comment\";\n  if (stream.match(multilineCommentsStart)) {\n    state.inComment = true;\n    return \"comment\";\n  }\n\n  // integer and float\n  if (stream.match(/^-?[0-9\\.]/, false)) {\n    if (stream.match(integers) || stream.match(floats)) return \"number\";\n  }\n\n  // string\n  if (stream.match(strings)) return \"string\";\n\n  // identifier\n  if (state.startDef && stream.match(identifiers)) return \"def\";\n\n  if (state.endDef && stream.match(identifiersEnd)) {\n    state.endDef = false;\n    return \"def\";\n  }\n\n  if (stream.match(keywords)) return \"keyword\";\n\n  if (stream.match(types)) {\n    var lastToken = state.lastToken;\n    var nextToken = (stream.match(/^\\s*(.+?)\\b/, false) || [])[1];\n\n    if (lastToken === \":\" || lastToken === \"implements\" ||\n        nextToken === \"implements\" || nextToken === \"=\") {\n      // Used as identifier\n      return \"builtin\";\n    } else {\n      // Used as type\n      return \"variable-3\";\n    }\n  }\n\n  if (stream.match(builtins)) return \"builtin\";\n  if (stream.match(atoms)) return \"atom\";\n  if (stream.match(identifiers)) return \"variable\";\n\n  // other\n  if (stream.match(singleOperators)) return \"operator\";\n\n  // unrecognized\n  stream.next();\n  return null;\n};\n\nCodeMirror.defineMode(\"webidl\", function() {\n  return {\n    startState: function() {\n      return {\n        // Is in multiline comment\n        inComment: false,\n        // Last non-whitespace, matched token\n        lastToken: \"\",\n        // Next token is a definition\n        startDef: false,\n        // Last token of the statement is a definition\n        endDef: false\n      };\n    },\n    token: function(stream, state) {\n      var style = readToken(stream, state);\n\n      if (style) {\n        var cur = stream.current();\n        state.lastToken = cur;\n        if (style === \"keyword\") {\n          state.startDef = startDefs.test(cur);\n          state.endDef = state.endDef || endDefs.test(cur);\n        } else {\n          state.startDef = false;\n        }\n      }\n\n      return style;\n    }\n  };\n});\n\nCodeMirror.defineMIME(\"text/x-webidl\", \"webidl\");\n});\n\n\n//# sourceURL=webpack:///./node_modules/codemirror/mode/webidl/webidl.js?");

/***/ })

}]);