(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[86],{

/***/ "./node_modules/codemirror/mode/q/q.js":
/*!*********************************************!*\
  !*** ./node_modules/codemirror/mode/q/q.js ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("// CodeMirror, copyright (c) by Marijn Haverbeke and others\n// Distributed under an MIT license: https://codemirror.net/LICENSE\n\n(function(mod) {\n  if (true) // CommonJS\n    mod(__webpack_require__(/*! ../../lib/codemirror */ \"./node_modules/codemirror/lib/codemirror.js\"));\n  else {}\n})(function(CodeMirror) {\n\"use strict\";\n\nCodeMirror.defineMode(\"q\",function(config){\n  var indentUnit=config.indentUnit,\n      curPunc,\n      keywords=buildRE([\"abs\",\"acos\",\"aj\",\"aj0\",\"all\",\"and\",\"any\",\"asc\",\"asin\",\"asof\",\"atan\",\"attr\",\"avg\",\"avgs\",\"bin\",\"by\",\"ceiling\",\"cols\",\"cor\",\"cos\",\"count\",\"cov\",\"cross\",\"csv\",\"cut\",\"delete\",\"deltas\",\"desc\",\"dev\",\"differ\",\"distinct\",\"div\",\"do\",\"each\",\"ej\",\"enlist\",\"eval\",\"except\",\"exec\",\"exit\",\"exp\",\"fby\",\"fills\",\"first\",\"fkeys\",\"flip\",\"floor\",\"from\",\"get\",\"getenv\",\"group\",\"gtime\",\"hclose\",\"hcount\",\"hdel\",\"hopen\",\"hsym\",\"iasc\",\"idesc\",\"if\",\"ij\",\"in\",\"insert\",\"inter\",\"inv\",\"key\",\"keys\",\"last\",\"like\",\"list\",\"lj\",\"load\",\"log\",\"lower\",\"lsq\",\"ltime\",\"ltrim\",\"mavg\",\"max\",\"maxs\",\"mcount\",\"md5\",\"mdev\",\"med\",\"meta\",\"min\",\"mins\",\"mmax\",\"mmin\",\"mmu\",\"mod\",\"msum\",\"neg\",\"next\",\"not\",\"null\",\"or\",\"over\",\"parse\",\"peach\",\"pj\",\"plist\",\"prd\",\"prds\",\"prev\",\"prior\",\"rand\",\"rank\",\"ratios\",\"raze\",\"read0\",\"read1\",\"reciprocal\",\"reverse\",\"rload\",\"rotate\",\"rsave\",\"rtrim\",\"save\",\"scan\",\"select\",\"set\",\"setenv\",\"show\",\"signum\",\"sin\",\"sqrt\",\"ss\",\"ssr\",\"string\",\"sublist\",\"sum\",\"sums\",\"sv\",\"system\",\"tables\",\"tan\",\"til\",\"trim\",\"txf\",\"type\",\"uj\",\"ungroup\",\"union\",\"update\",\"upper\",\"upsert\",\"value\",\"var\",\"view\",\"views\",\"vs\",\"wavg\",\"where\",\"where\",\"while\",\"within\",\"wj\",\"wj1\",\"wsum\",\"xasc\",\"xbar\",\"xcol\",\"xcols\",\"xdesc\",\"xexp\",\"xgroup\",\"xkey\",\"xlog\",\"xprev\",\"xrank\"]),\n      E=/[|/&^!+:\\\\\\-*%$=~#;@><,?_\\'\\\"\\[\\(\\]\\)\\s{}]/;\n  function buildRE(w){return new RegExp(\"^(\"+w.join(\"|\")+\")$\");}\n  function tokenBase(stream,state){\n    var sol=stream.sol(),c=stream.next();\n    curPunc=null;\n    if(sol)\n      if(c==\"/\")\n        return(state.tokenize=tokenLineComment)(stream,state);\n      else if(c==\"\\\\\"){\n        if(stream.eol()||/\\s/.test(stream.peek()))\n          return stream.skipToEnd(),/^\\\\\\s*$/.test(stream.current())?(state.tokenize=tokenCommentToEOF)(stream):state.tokenize=tokenBase,\"comment\";\n        else\n          return state.tokenize=tokenBase,\"builtin\";\n      }\n    if(/\\s/.test(c))\n      return stream.peek()==\"/\"?(stream.skipToEnd(),\"comment\"):\"whitespace\";\n    if(c=='\"')\n      return(state.tokenize=tokenString)(stream,state);\n    if(c=='`')\n      return stream.eatWhile(/[A-Za-z\\d_:\\/.]/),\"symbol\";\n    if((\".\"==c&&/\\d/.test(stream.peek()))||/\\d/.test(c)){\n      var t=null;\n      stream.backUp(1);\n      if(stream.match(/^\\d{4}\\.\\d{2}(m|\\.\\d{2}([DT](\\d{2}(:\\d{2}(:\\d{2}(\\.\\d{1,9})?)?)?)?)?)/)\n      || stream.match(/^\\d+D(\\d{2}(:\\d{2}(:\\d{2}(\\.\\d{1,9})?)?)?)/)\n      || stream.match(/^\\d{2}:\\d{2}(:\\d{2}(\\.\\d{1,9})?)?/)\n      || stream.match(/^\\d+[ptuv]{1}/))\n        t=\"temporal\";\n      else if(stream.match(/^0[NwW]{1}/)\n      || stream.match(/^0x[\\da-fA-F]*/)\n      || stream.match(/^[01]+[b]{1}/)\n      || stream.match(/^\\d+[chijn]{1}/)\n      || stream.match(/-?\\d*(\\.\\d*)?(e[+\\-]?\\d+)?(e|f)?/))\n        t=\"number\";\n      return(t&&(!(c=stream.peek())||E.test(c)))?t:(stream.next(),\"error\");\n    }\n    if(/[A-Za-z]|\\./.test(c))\n      return stream.eatWhile(/[A-Za-z._\\d]/),keywords.test(stream.current())?\"keyword\":\"variable\";\n    if(/[|/&^!+:\\\\\\-*%$=~#;@><\\.,?_\\']/.test(c))\n      return null;\n    if(/[{}\\(\\[\\]\\)]/.test(c))\n      return null;\n    return\"error\";\n  }\n  function tokenLineComment(stream,state){\n    return stream.skipToEnd(),/\\/\\s*$/.test(stream.current())?(state.tokenize=tokenBlockComment)(stream,state):(state.tokenize=tokenBase),\"comment\";\n  }\n  function tokenBlockComment(stream,state){\n    var f=stream.sol()&&stream.peek()==\"\\\\\";\n    stream.skipToEnd();\n    if(f&&/^\\\\\\s*$/.test(stream.current()))\n      state.tokenize=tokenBase;\n    return\"comment\";\n  }\n  function tokenCommentToEOF(stream){return stream.skipToEnd(),\"comment\";}\n  function tokenString(stream,state){\n    var escaped=false,next,end=false;\n    while((next=stream.next())){\n      if(next==\"\\\"\"&&!escaped){end=true;break;}\n      escaped=!escaped&&next==\"\\\\\";\n    }\n    if(end)state.tokenize=tokenBase;\n    return\"string\";\n  }\n  function pushContext(state,type,col){state.context={prev:state.context,indent:state.indent,col:col,type:type};}\n  function popContext(state){state.indent=state.context.indent;state.context=state.context.prev;}\n  return{\n    startState:function(){\n      return{tokenize:tokenBase,\n             context:null,\n             indent:0,\n             col:0};\n    },\n    token:function(stream,state){\n      if(stream.sol()){\n        if(state.context&&state.context.align==null)\n          state.context.align=false;\n        state.indent=stream.indentation();\n      }\n      //if (stream.eatSpace()) return null;\n      var style=state.tokenize(stream,state);\n      if(style!=\"comment\"&&state.context&&state.context.align==null&&state.context.type!=\"pattern\"){\n        state.context.align=true;\n      }\n      if(curPunc==\"(\")pushContext(state,\")\",stream.column());\n      else if(curPunc==\"[\")pushContext(state,\"]\",stream.column());\n      else if(curPunc==\"{\")pushContext(state,\"}\",stream.column());\n      else if(/[\\]\\}\\)]/.test(curPunc)){\n        while(state.context&&state.context.type==\"pattern\")popContext(state);\n        if(state.context&&curPunc==state.context.type)popContext(state);\n      }\n      else if(curPunc==\".\"&&state.context&&state.context.type==\"pattern\")popContext(state);\n      else if(/atom|string|variable/.test(style)&&state.context){\n        if(/[\\}\\]]/.test(state.context.type))\n          pushContext(state,\"pattern\",stream.column());\n        else if(state.context.type==\"pattern\"&&!state.context.align){\n          state.context.align=true;\n          state.context.col=stream.column();\n        }\n      }\n      return style;\n    },\n    indent:function(state,textAfter){\n      var firstChar=textAfter&&textAfter.charAt(0);\n      var context=state.context;\n      if(/[\\]\\}]/.test(firstChar))\n        while (context&&context.type==\"pattern\")context=context.prev;\n      var closing=context&&firstChar==context.type;\n      if(!context)\n        return 0;\n      else if(context.type==\"pattern\")\n        return context.col;\n      else if(context.align)\n        return context.col+(closing?0:1);\n      else\n        return context.indent+(closing?0:indentUnit);\n    }\n  };\n});\nCodeMirror.defineMIME(\"text/x-q\",\"q\");\n\n});\n\n\n//# sourceURL=webpack:///./node_modules/codemirror/mode/q/q.js?");

/***/ })

}]);