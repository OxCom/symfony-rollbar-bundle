# SymfonyRollbar bundle for Symfony Framework
[![Latest Stable Version](https://poser.pugx.org/oxcom/symfony-rollbar-bundle/v/stable)](https://packagist.org/packages/oxcom/symfony-rollbar-bundle)
[![Total Downloads](https://poser.pugx.org/oxcom/symfony-rollbar-bundle/downloads)](https://packagist.org/packages/oxcom/symfony-rollbar-bundle)
[![codecov](https://codecov.io/gh/OxCom/symfony3-rollbar-bundle/branch/master/graph/badge.svg)](https://codecov.io/gh/OxCom/symfony3-rollbar-bundle)
[![Build Status](https://travis-ci.org/OxCom/symfony3-rollbar-bundle.svg?branch=master)](https://travis-ci.org/OxCom/symfony3-rollbar-bundle)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Bundle for Symfony Framework (v2.8.x, 3.x, 4.x) that integrates Rollbar tracker

Find all documentation here [here](https://github.com/OxCom/symfony3-rollbar-bundle/tree/master/Resources/doc)

# Install
1. Add bundle as dependency
    ```bash
    $ composer require oxcom/symfony-rollbar-bundle
    ```
2. Provide configuration for it
    ```yaml
    symfony_rollbar:
            enable: true
            exclude:
                - \Symfony\Component\Debug\Exception\FatalErrorException
                - \AppBundle\Exceptions\MyAwesomeException
            rollbar:
                access_token: 'some-secret-token-here'
    ```

# TODO
- Add integration for [Rollbar.JS](https://rollbar.com/docs/notifier/rollbar.js/) 
- More docs

# Bugs and Issues
Please, if You found a bug or something, that is not working properly, contact me and tell what's wrong. It's nice to have an example how to reproduce a bug, or any idea how to fix it in Your request. I'll take care about it ASAP.

require symfony/symfony:${SYMFONY_VERSION} --no-update