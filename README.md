# Set up notes

* Initialize submodules: `git submodule update --init --recursive`
* Put config in place: `cp config.inc.php.sample config.inc.php; cp .env.sample /env`
* Edit the `.env` file and make sure the two directories point to your OJS file storage (or unset)
* Run `script/up`
* Import DB: `cat ignore/ojs.sql | docker compose exec -T mysql mysql -umysql -pmysql mysql`

# Reset Password

* Start mailcatcher on your host
* Use password reset flow for any account in `users` table
* Temporary password will be sent in second email

# Log in

There's a redirect loop unless you use this URL:
`http://localhost:8080/index.php/index/login?subi=no`


# Notes for Setting Up a New Environment

1. Make sure the cdlexport plugin is registered in the `versions` table, with `lazy_load` set to 0
2. Add settings for section to the config file for HTTP requests to cdlexport endpoints (see `config.inc.php.sample`)