(function(){var R=!1,Q=this;function P(i,l){var f=i.split("."),k=Q;!(f[0] in k)&&k.execScript&&k.execScript("var "+f[0]);for(var j;f.length&&(j=f.shift());){!f.length&&void 0!==l?k[j]=l:k=k[j]?k[j]:k[j]={}}}var O=Array.prototype,N=O.forEach?function(i,j,f){O.forEach.call(i,j,f)}:function(i,n,f){for(var l=i.length,k="string"==typeof i?i.split(""):i,j=0;j<l;j++){j in k&&n.call(f,k[j],j,i)}};function M(i,n){this.c={};this.a=[];var f=arguments.length;if(1<f){if(f%2){throw Error("Uneven number of arguments")}for(var l=0;l<f;l+=2){this.set(arguments[l],arguments[l+1])}}else{if(i){var k;if(i instanceof M){L(i);l=i.a.concat();L(i);k=[];for(f=0;f<i.a.length;f++){k.push(i.c[i.a[f]])}}else{var f=[],j=0;for(l in i){f[j++]=l}l=f;f=[];j=0;for(k in i){f[j++]=i[k]}k=f}for(f=0;f<l.length;f++){this.set(l[f],k[f])}}}}M.prototype.e=0;M.prototype.o=0;function L(i){if(i.e!=i.a.length){for(var l=0,f=0;l<i.a.length;){var k=i.a[l];y(i.c,k)&&(i.a[f++]=k);l++}i.a.length=f}if(i.e!=i.a.length){for(var j={},f=l=0;l<i.a.length;){k=i.a[l],y(j,k)||(i.a[f++]=k,j[k]=1),l++}i.a.length=f}}M.prototype.get=function(f,i){return y(this.c,f)?this.c[f]:i};M.prototype.set=function(f,i){y(this.c,f)||(this.e++,this.a.push(f),this.o++);this.c[f]=i};function y(f,i){return Object.prototype.hasOwnProperty.call(f,i)}var q,o,h,g;function a(){return Q.navigator?Q.navigator.userAgent:null}g=h=o=q=R;var K;if(K=a()){var F=Q.navigator;q=0==K.indexOf("Opera");o=!q&&-1!=K.indexOf("MSIE");h=!q&&-1!=K.indexOf("WebKit");g=!q&&!h&&"Gecko"==F.product}var x=o,w=g,m=h;var e;if(q&&Q.opera){var d=Q.opera.version;"function"==typeof d&&d()}else{w?e=/rv\:([^\);]+)(\)|;)/:x?e=/MSIE\s+([^\);]+)(\)|;)/:m&&(e=/WebKit\/(\S+)/),e&&e.exec(a())}function c(f,i){this.b=f||{d:"",prefix:"",host:"",scheme:""};this.h(i||{})}c.f=function(){return c.j?c.j:c.j=new c};c.prototype.h=function(f){this.g=new M(f)};c.prototype.k=function(f){this.b.d=f};c.prototype.n=function(){return this.b.d};c.prototype.l=function(f){this.b.prefix=f};function b(i,n,f,l){var k,j=RegExp(/\[\]$/);if(f instanceof Array){N(f,function(p,r){j.test(n)?l(n,p):b(i,n+"["+("object"===typeof p?r:"")+"]",p,l)})}else{if("object"===typeof f){for(k in f){b(i,n+"["+k+"]",f[k],l)}}else{l(n,f)}}}c.prototype.i=function(f){var i=this.b.prefix+f;if(y(this.g.c,i)){f=i}else{if(!y(this.g.c,f)){throw Error('The route "'+f+'" does not exist.')}}return this.g.get(f)};c.prototype.m=function(t,n,r){var l=this.i(t),k=n||{},j={},u;for(u in k){j[u]=k[u]}var i="",f=!0;N(l.tokens,function(A){if("text"===A[0]){i=A[1]+i,f=R}else{if("variable"===A[0]){var B=A[3] in l.defaults;if(R===f||!B||A[3] in k&&k[A[3]]!=l.defaults[A[3]]){if(A[3] in k){var B=k[A[3]],z=A[3];z in j&&delete j[z]}else{if(B){B=l.defaults[A[3]]}else{if(f){return}throw Error('The route "'+t+'" requires the parameter "'+A[3]+'".')}}if(!(!0===B||R===B||""===B)||!f){z=encodeURIComponent(B).replace(/%2F/g,"/"),"null"===z&&null===B&&(z=""),i=A[1]+z+i}f=R}else{B&&(A=A[3],A in j&&delete j[A])}}else{throw Error('The token type "'+A[0]+'" is not supported.')}}});""===i&&(i="/");i=this.b.d+i;"_scheme" in l.requirements?this.b.scheme!=l.requirements._scheme&&(i=l.requirements._scheme+"://"+this.b.host+i):!0===r&&(i=this.b.scheme+"://"+this.b.host+i);var n=0,s;for(s in j){n++}if(0<n){var p,v=[];s=function(A,z){z="function"===typeof z?z():z;v.push(encodeURIComponent(A)+"="+encodeURIComponent(null===z?"":z))};for(p in j){b(this,p,j[p],s)}i=i+"?"+v.join("&").replace(/%20/g,"+")}return i};P("fos.Router",c);P("fos.Router.setData",function(f){var i=c.f();i.k(f.base_url);i.h(f.routes);"prefix" in f&&i.l(f.prefix);i.b.host=f.host;i.b.scheme=f.scheme});c.getInstance=c.f;c.prototype.setRoutes=c.prototype.h;c.prototype.setBaseUrl=c.prototype.k;c.prototype.getBaseUrl=c.prototype.n;c.prototype.generate=c.prototype.m;c.prototype.setPrefix=c.prototype.l;c.prototype.getRoute=c.prototype.i;window.Routing=c.f()})();