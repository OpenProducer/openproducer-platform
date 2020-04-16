this.wp=this.wp||{},this.wp.blockSerializationSpecParser=function(t){var r={};function e(n){if(r[n])return r[n].exports;var u=r[n]={i:n,l:!1,exports:{}};return t[n].call(u.exports,u,u.exports,e),u.l=!0,u.exports}return e.m=t,e.c=r,e.d=function(t,r,n){e.o(t,r)||Object.defineProperty(t,r,{enumerable:!0,get:n})},e.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},e.t=function(t,r){if(1&r&&(t=e(t)),8&r)return t;if(4&r&&"object"==typeof t&&t&&t.__esModule)return t;var n=Object.create(null);if(e.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:t}),2&r&&"string"!=typeof t)for(var u in t)e.d(n,u,function(r){return t[r]}.bind(null,u));return n},e.n=function(t){var r=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(r,"a",r),r},e.o=function(t,r){return Object.prototype.hasOwnProperty.call(t,r)},e.p="",e(e.s=290)}({290:function(t,r,e){"use strict";function n(t,r,e,u){this.message=t,this.expected=r,this.found=e,this.location=u,this.name="SyntaxError","function"==typeof Error.captureStackTrace&&Error.captureStackTrace(this,n)}!function(t,r){function e(){this.constructor=t}e.prototype=r.prototype,t.prototype=new e}(n,Error),n.buildMessage=function(t,r){var e={literal:function(t){return'"'+u(t.text)+'"'},class:function(t){var r,e="";for(r=0;r<t.parts.length;r++)e+=t.parts[r]instanceof Array?o(t.parts[r][0])+"-"+o(t.parts[r][1]):o(t.parts[r]);return"["+(t.inverted?"^":"")+e+"]"},any:function(t){return"any character"},end:function(t){return"end of input"},other:function(t){return t.description}};function n(t){return t.charCodeAt(0).toString(16).toUpperCase()}function u(t){return t.replace(/\\/g,"\\\\").replace(/"/g,'\\"').replace(/\0/g,"\\0").replace(/\t/g,"\\t").replace(/\n/g,"\\n").replace(/\r/g,"\\r").replace(/[\x00-\x0F]/g,(function(t){return"\\x0"+n(t)})).replace(/[\x10-\x1F\x7F-\x9F]/g,(function(t){return"\\x"+n(t)}))}function o(t){return t.replace(/\\/g,"\\\\").replace(/\]/g,"\\]").replace(/\^/g,"\\^").replace(/-/g,"\\-").replace(/\0/g,"\\0").replace(/\t/g,"\\t").replace(/\n/g,"\\n").replace(/\r/g,"\\r").replace(/[\x00-\x0F]/g,(function(t){return"\\x0"+n(t)})).replace(/[\x10-\x1F\x7F-\x9F]/g,(function(t){return"\\x"+n(t)}))}return"Expected "+function(t){var r,n,u,o=new Array(t.length);for(r=0;r<t.length;r++)o[r]=(u=t[r],e[u.type](u));if(o.sort(),o.length>0){for(r=1,n=1;r<o.length;r++)o[r-1]!==o[r]&&(o[n]=o[r],n++);o.length=n}switch(o.length){case 1:return o[0];case 2:return o[0]+" or "+o[1];default:return o.slice(0,-1).join(", ")+", or "+o[o.length-1]}}(t)+" but "+function(t){return t?'"'+u(t)+'"':"end of input"}(r)+" found."},t.exports={SyntaxError:n,parse:function(t,r){r=void 0!==r?r:{};var e,u={},o={Block_List:J},i=J,c={type:"any"},a=function(t,r,e){return[r,e]},s=function(t,r,e){return function(t,r,e){var n,u,o,i,c,a=[];t.length&&a.push(Q(t));for(n=0,u=r.length;n<u;n++)i=r[n],c=i[0],o=i[1],a.push(c),o.length&&a.push(Q(o));e.length&&a.push(Q(e));return a}(t,r,e)},l="\x3c!--",f=T("\x3c!--",!1),h=T("wp:",!1),p=function(t,r){return r},g=T("/--\x3e",!1),d=T("--\x3e",!1),v=T("/wp:",!1),b=T("/",!1),x=/^[a-z]/,A=P([["a","z"]],!1,!1),y=/^[a-z0-9_\-]/,m=P([["a","z"],["0","9"],"_","-"],!1,!1),w=z("JSON-encoded attributes embedded in a block's opening comment"),C=T("{",!1),k=T("}",!1),S=function(t){return function(t){try{return JSON.parse(t)}catch(t){return null}}(t)},M=/^[ \t\r\n]/,O=P([" ","\t","\r","\n"],!1,!1),_=0,j=[{line:1,column:1}],F=0,N=[],E=0;if("startRule"in r){if(!(r.startRule in o))throw new Error("Can't start parsing from rule \""+r.startRule+'".');i=o[r.startRule]}function T(t,r){return{type:"literal",text:t,ignoreCase:r}}function P(t,r,e){return{type:"class",parts:t,inverted:r,ignoreCase:e}}function z(t){return{type:"other",description:t}}function B(r){var e,n=j[r];if(n)return n;for(e=r-1;!j[e];)e--;for(n={line:(n=j[e]).line,column:n.column};e<r;)10===t.charCodeAt(e)?(n.line++,n.column=1):n.column++,e++;return j[r]=n,n}function L(t,r){var e=B(t),n=B(r);return{start:{offset:t,line:e.line,column:e.column},end:{offset:r,line:n.line,column:n.column}}}function R(t){_<F||(_>F&&(F=_,N=[]),N.push(t))}function H(t,r,e){return new n(n.buildMessage(t,r),t,r,e)}function J(){var r,e,n,o,i,l,f,h,p,g;for(r=_,e=_,n=[],o=_,i=_,E++,l=U(),E--,l===u?i=void 0:(_=i,i=u),i!==u?(t.length>_?(l=t.charAt(_),_++):(l=u,0===E&&R(c)),l!==u?o=i=[i,l]:(_=o,o=u)):(_=o,o=u);o!==u;)n.push(o),o=_,i=_,E++,l=U(),E--,l===u?i=void 0:(_=i,i=u),i!==u?(t.length>_?(l=t.charAt(_),_++):(l=u,0===E&&R(c)),l!==u?o=i=[i,l]:(_=o,o=u)):(_=o,o=u);if((e=n!==u?t.substring(e,_):n)!==u){if(n=[],o=_,(i=U())!==u){for(l=_,f=[],h=_,p=_,E++,g=U(),E--,g===u?p=void 0:(_=p,p=u),p!==u?(t.length>_?(g=t.charAt(_),_++):(g=u,0===E&&R(c)),g!==u?h=p=[p,g]:(_=h,h=u)):(_=h,h=u);h!==u;)f.push(h),h=_,p=_,E++,g=U(),E--,g===u?p=void 0:(_=p,p=u),p!==u?(t.length>_?(g=t.charAt(_),_++):(g=u,0===E&&R(c)),g!==u?h=p=[p,g]:(_=h,h=u)):(_=h,h=u);(l=f!==u?t.substring(l,_):f)!==u?(o,o=i=a(0,i,l)):(_=o,o=u)}else _=o,o=u;for(;o!==u;)if(n.push(o),o=_,(i=U())!==u){for(l=_,f=[],h=_,p=_,E++,g=U(),E--,g===u?p=void 0:(_=p,p=u),p!==u?(t.length>_?(g=t.charAt(_),_++):(g=u,0===E&&R(c)),g!==u?h=p=[p,g]:(_=h,h=u)):(_=h,h=u);h!==u;)f.push(h),h=_,p=_,E++,g=U(),E--,g===u?p=void 0:(_=p,p=u),p!==u?(t.length>_?(g=t.charAt(_),_++):(g=u,0===E&&R(c)),g!==u?h=p=[p,g]:(_=h,h=u)):(_=h,h=u);(l=f!==u?t.substring(l,_):f)!==u?(o,o=i=a(0,i,l)):(_=o,o=u)}else _=o,o=u;if(n!==u){for(o=_,i=[],t.length>_?(l=t.charAt(_),_++):(l=u,0===E&&R(c));l!==u;)i.push(l),t.length>_?(l=t.charAt(_),_++):(l=u,0===E&&R(c));(o=i!==u?t.substring(o,_):i)!==u?(r,r=e=s(e,n,o)):(_=r,r=u)}else _=r,r=u}else _=r,r=u;return r}function U(){var r;return(r=function(){var r,e,n,o,i,c;r=_,t.substr(_,4)===l?(e=l,_+=4):(e=u,0===E&&R(f));e!==u&&K()!==u?("wp:"===t.substr(_,3)?(n="wp:",_+=3):(n=u,0===E&&R(h)),n!==u&&(o=D())!==u&&K()!==u?(i=_,(c=I())!==u&&K()!==u?(i,c=p(0,c),i=c):(_=i,i=u),i===u&&(i=null),i!==u?("/--\x3e"===t.substr(_,4)?(c="/--\x3e",_+=4):(c=u,0===E&&R(g)),c!==u?(r,r=e={blockName:o,attrs:i||{},innerBlocks:[],innerHTML:"",innerContent:[]}):(_=r,r=u)):(_=r,r=u)):(_=r,r=u)):(_=r,r=u);return r}())===u&&(r=function(){var r,e,n,o,i,a,s,g,v;if(r=_,(e=function(){var r,e,n,o,i,c;r=_,t.substr(_,4)===l?(e=l,_+=4):(e=u,0===E&&R(f));e!==u&&K()!==u?("wp:"===t.substr(_,3)?(n="wp:",_+=3):(n=u,0===E&&R(h)),n!==u&&(o=D())!==u&&K()!==u?(i=_,(c=I())!==u&&K()!==u?(i,c=p(0,c),i=c):(_=i,i=u),i===u&&(i=null),i!==u?("--\x3e"===t.substr(_,3)?(c="--\x3e",_+=3):(c=u,0===E&&R(d)),c!==u?(r,r=e={blockName:o,attrs:i||{}}):(_=r,r=u)):(_=r,r=u)):(_=r,r=u)):(_=r,r=u);return r}())!==u){if(n=[],(o=U())===u){if(o=_,i=[],a=_,s=_,E++,g=U(),E--,g===u?s=void 0:(_=s,s=u),s!==u?(g=_,E++,v=q(),E--,v===u?g=void 0:(_=g,g=u),g!==u?(t.length>_?(v=t.charAt(_),_++):(v=u,0===E&&R(c)),v!==u?a=s=[s,g,v]:(_=a,a=u)):(_=a,a=u)):(_=a,a=u),a!==u)for(;a!==u;)i.push(a),a=_,s=_,E++,g=U(),E--,g===u?s=void 0:(_=s,s=u),s!==u?(g=_,E++,v=q(),E--,v===u?g=void 0:(_=g,g=u),g!==u?(t.length>_?(v=t.charAt(_),_++):(v=u,0===E&&R(c)),v!==u?a=s=[s,g,v]:(_=a,a=u)):(_=a,a=u)):(_=a,a=u);else i=u;o=i!==u?t.substring(o,_):i}for(;o!==u;)if(n.push(o),(o=U())===u){if(o=_,i=[],a=_,s=_,E++,g=U(),E--,g===u?s=void 0:(_=s,s=u),s!==u?(g=_,E++,v=q(),E--,v===u?g=void 0:(_=g,g=u),g!==u?(t.length>_?(v=t.charAt(_),_++):(v=u,0===E&&R(c)),v!==u?a=s=[s,g,v]:(_=a,a=u)):(_=a,a=u)):(_=a,a=u),a!==u)for(;a!==u;)i.push(a),a=_,s=_,E++,g=U(),E--,g===u?s=void 0:(_=s,s=u),s!==u?(g=_,E++,v=q(),E--,v===u?g=void 0:(_=g,g=u),g!==u?(t.length>_?(v=t.charAt(_),_++):(v=u,0===E&&R(c)),v!==u?a=s=[s,g,v]:(_=a,a=u)):(_=a,a=u)):(_=a,a=u);else i=u;o=i!==u?t.substring(o,_):i}n!==u&&(o=q())!==u?(r,b=e,x=function(t){var r,e,n,u="",o=[],i=[];for(r=0,e=t.length;r<e;r++)"string"==typeof(n=t[r])?(u+=n,i.push(n)):(o.push(n),i.push(null));return[u,o,i]}(n),A=x[0],y=x[1],m=x[2],e={blockName:b.blockName,attrs:b.attrs,innerBlocks:y,innerHTML:A,innerContent:m},r=e):(_=r,r=u)}else _=r,r=u;var b,x,A,y,m;return r}()),r}function q(){var r,e,n,o,i;return r=_,t.substr(_,4)===l?(e=l,_+=4):(e=u,0===E&&R(f)),e!==u&&K()!==u?("/wp:"===t.substr(_,4)?(n="/wp:",_+=4):(n=u,0===E&&R(v)),n!==u&&(o=D())!==u&&K()!==u?("--\x3e"===t.substr(_,3)?(i="--\x3e",_+=3):(i=u,0===E&&R(d)),i!==u?(r,r=e={blockName:o}):(_=r,r=u)):(_=r,r=u)):(_=r,r=u),r}function D(){var r;return(r=function(){var r,e,n,o,i;r=_,e=_,(n=G())!==u?(47===t.charCodeAt(_)?(o="/",_++):(o=u,0===E&&R(b)),o!==u&&(i=G())!==u?e=n=[n,o,i]:(_=e,e=u)):(_=e,e=u);r=e!==u?t.substring(r,_):e;return r}())===u&&(r=function(){var r,e,n;r=_,e=_,e=(n=G())!==u?t.substring(e,_):n;e!==u&&(r,e="core/"+e);return r=e}()),r}function G(){var r,e,n,o,i;if(r=_,e=_,x.test(t.charAt(_))?(n=t.charAt(_),_++):(n=u,0===E&&R(A)),n!==u){for(o=[],y.test(t.charAt(_))?(i=t.charAt(_),_++):(i=u,0===E&&R(m));i!==u;)o.push(i),y.test(t.charAt(_))?(i=t.charAt(_),_++):(i=u,0===E&&R(m));o!==u?e=n=[n,o]:(_=e,e=u)}else _=e,e=u;return r=e!==u?t.substring(r,_):e}function I(){var r,e,n,o,i,a,s,l,f,h,p,g,v;if(E++,r=_,e=_,n=_,123===t.charCodeAt(_)?(o="{",_++):(o=u,0===E&&R(C)),o!==u){for(i=[],a=_,s=_,E++,l=_,125===t.charCodeAt(_)?(f="}",_++):(f=u,0===E&&R(k)),f!==u&&(h=K())!==u&&(p="")!==u?(47===t.charCodeAt(_)?(g="/",_++):(g=u,0===E&&R(b)),g===u&&(g=null),g!==u?("--\x3e"===t.substr(_,3)?(v="--\x3e",_+=3):(v=u,0===E&&R(d)),v!==u?l=f=[f,h,p,g,v]:(_=l,l=u)):(_=l,l=u)):(_=l,l=u),E--,l===u?s=void 0:(_=s,s=u),s!==u?(t.length>_?(l=t.charAt(_),_++):(l=u,0===E&&R(c)),l!==u?a=s=[s,l]:(_=a,a=u)):(_=a,a=u);a!==u;)i.push(a),a=_,s=_,E++,l=_,125===t.charCodeAt(_)?(f="}",_++):(f=u,0===E&&R(k)),f!==u&&(h=K())!==u&&(p="")!==u?(47===t.charCodeAt(_)?(g="/",_++):(g=u,0===E&&R(b)),g===u&&(g=null),g!==u?("--\x3e"===t.substr(_,3)?(v="--\x3e",_+=3):(v=u,0===E&&R(d)),v!==u?l=f=[f,h,p,g,v]:(_=l,l=u)):(_=l,l=u)):(_=l,l=u),E--,l===u?s=void 0:(_=s,s=u),s!==u?(t.length>_?(l=t.charAt(_),_++):(l=u,0===E&&R(c)),l!==u?a=s=[s,l]:(_=a,a=u)):(_=a,a=u);i!==u?(125===t.charCodeAt(_)?(a="}",_++):(a=u,0===E&&R(k)),a!==u?n=o=[o,i,a]:(_=n,n=u)):(_=n,n=u)}else _=n,n=u;return(e=n!==u?t.substring(e,_):n)!==u&&(r,e=S(e)),E--,(r=e)===u&&(e=u,0===E&&R(w)),r}function K(){var r,e;if(r=[],M.test(t.charAt(_))?(e=t.charAt(_),_++):(e=u,0===E&&R(O)),e!==u)for(;e!==u;)r.push(e),M.test(t.charAt(_))?(e=t.charAt(_),_++):(e=u,0===E&&R(O));else r=u;return r}function Q(t){return t.length&&{blockName:null,attrs:{},innerBlocks:[],innerHTML:t,innerContent:[t]}}if((e=i())!==u&&_===t.length)return e;throw e!==u&&_<t.length&&R({type:"end"}),H(N,F<t.length?t.charAt(F):null,F<t.length?L(F,F+1):L(F,F))}}}});