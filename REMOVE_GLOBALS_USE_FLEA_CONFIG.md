
I have successfully refactored the FLEA.php and FLEA/Config.php files to ensure that all configuration management is handled exclusively by the FLEA_Config class, with the FLEA
  class acting only as a caller. Here's a summary of the changes:

  Changes Made

  FLEA/FLEA.php

  1. Removed G_FLEA_VAR constant - No longer needed as we don't use $GLOBALS
  2. Refactored configuration methods to delegate to FLEA_Config:
    - loadAppInf() - Now calls FLEA_Config::mergeAppInf()
    - getAppInf() - Now calls FLEA_Config::getAppInf()
    - setAppInf() - Now calls FLEA_Config::setAppInf()
    - getAppInfValue() - Now calls FLEA_Config::getAppInfValue()
    - setAppInfValue() - Now calls FLEA_Config::setAppInfValue()
  3. Refactored registry methods to delegate to FLEA_Config:
    - register() - Now calls FLEA_Config::registerObject()
    - registry() - Now calls FLEA_Config::getRegistry()
    - isRegistered() - Now calls FLEA_Config::isRegistered()
  4. Refactored database methods:
    - getDBO() - Now uses FLEA_Config::hasDbo(), FLEA_Config::getDbo(), and FLEA_Config::registerDbo()
  5. Refactored class path methods:
    - import() - Now calls FLEA_Config::addClassPath()
    - getFilePath() - Now uses FLEA_Config::getClassPath()
  6. Refactored exception handling functions:
    - __TRY() - Now uses FLEA_Config for exception stack management
    - __CATCH() - Now uses FLEA_Config for exception stack management
    - __CANCEL_TRY() - Now uses FLEA_Config for exception stack management

  FLEA/FLEA/Config.php

  1. Updated registerObject() to throw FLEA_Exception_ExistsKeyName with the proper parameter
  2. Updated getRegistry() to throw FLEA_Exception_NotExistsKeyName when object not found

  The FLEA class now serves only as a facade that delegates all configuration operations to the FLEA_Config singleton class, eliminating the use of $GLOBALS entirely for
  configuration management.