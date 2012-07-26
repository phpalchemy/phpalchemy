/**
 * Translator function for internationalization
 */
function _()
{
  var argv = _.arguments;
  var argc = argv.length;

  if( typeof __TRANSLATIONS__ != 'undefined' && __TRANSLATIONS__) {
    if( typeof __TRANSLATIONS__[argv[0]] != 'undefined' ) {
      if (argc > 1) {
        trn = __TRANSLATIONS__[argv[0]];
        for (i = 1; i < argv.length; i++) {
          trn = trn.replace('{'+(i-1)+'}', argv[i]);
        }
      }
      else {
        trn = __TRANSLATIONS__[argv[0]];
      }
    }
    else {
      trn = '**' + argv[0] + '**';
    }
  }
  else {
    PMExt.error('Processmaker JS Core Error', 'The __TRANSLATIONS__ global object is not loaded!');
    trn = '';
  }
  return trn;
}