
/*------------------------------------------------------------------------
# Common support functions for compatibility mode
# ------------------------------------------------------------------------
# author:    Dmitry Zhuk
# copyright: Copyright (C) 2013 zhuk.cc. All Rights Reserved.
# license:   http://creativecommons.org/licenses/by-nc-nd/4.0/
#            Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License.
# website:   http://zhuk.cc
# Technical Support Forum: http://forum.zhuk.cc/
# version: 11.0.2
-------------------------------------------------------------------------*/
function eR(fk,dU){return(' '+fk.className+' ').indexOf(' '+dU+' ')> -1;};function ez(fk,dU){if(!eR(fk,dU)){if(fk.className==""){fk.className+=dU;}else{fk.className+=" "+dU;}}};function eI(fk,dU){if(eR(fk,dU)){var ci;ci=new RegExp('(?:^|\\s)'+dU+'(?:\\s|$)');fk.className=fk.className.replace(ci,' ');if(fk.className==" "){fk.className="";}}};function fj(fk,dU){if(eR(fk,dU)){eI(fk,dU);}else{ez(fk,dU);}}; 