# Polylang-toggler
This is an early-stage plugin for WordPress + Polylang. It's nothing fancy, but it adds a new type of language switcher; a toggler that cycles between languages trying to preserve URL.

## Disclaimer
This is a hobby project, and hacked together with very little time. Not every use case has been tested, but as always, you are welcome to give it a try and provide some feedback if it doesn't.

## Motivation
I know this is kinda pointless (people usually know which language they prefer, so why would anyone want to cycle through all?), but there is a use-case out there (obviously, as this plugin is created as a hobby project from a real customer request);

This is a plugin for you, if all of the following apply:
- you have only a couple of pages translated
- you have only a couple of languages
- you absolutely don't want the flags (or country names)

At least I got to use couple of less-used WP API's (endpoints & menu item metaboxes), and who knows, maybe this someday helps someone?

## How it works
1. New menu item type is created, Language toggler
    - You can customize the menu item text, by default it is a combined string of slugs from installed languages (ie. FI | EN | SV )
2. Thats it. Everyhting else is handled within hooks. The said menu item link is simply a current page URL with query string, ?cycle-language.

The plugin registers an endpoint for every page (and hereby a query string, endpoints are just a fancier way of using query variables), and requesting a page with said endpoint simply reloads the page with the next language. You can access the endpoint by two ways;
- requesting URL with /cycle-language- postfix, ie. https://domain.tld/2020/09/page1/cycle-language
- requesting URL with cycle-language in query string, ie. https://domain.tld/2020/09/page1?cycle-language

If no translation is found for current post, it falls back to home page.

## TODO
- Handle archive pages etc, currently it only checks if post is singlugar & translation exits.
