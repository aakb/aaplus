# Aa+

Rådgiverværktøjet stilles vederlagsfrit til rådighed under henvisning til, at
dette vil bidrage til at fremme en lovlig kommunal interesse i
miljøet/energibesparelser, hvilket sker på baggrund af

Hortens juridiske vurdering (10.5.2019) af de kommunalretlige forhold vedrørende
Rådgiverværktøjet og efterfølgende interne vurdering af underleverandørers
immaterielle rettigheder jf. kontrakter og øvrige sagsdokumenter.

# Release

The project follows git flow for development and releases. To finish a release, first:

1. Update aaplus-release-ver field in parameters.yml to the correct number.
2. Finish and tag the release-branch with the same release number.

Then on the server:

```
git fetch
git checkout vX.X.X
composer install
php app/console doctrine:migrations:migrate
php app/console cache:clear --env=prod

chmod -R g+w web/filer/
chmod -R g+w web/uploads/
chown -R deploy:www-data web/

Clone the code:

```sh
git clone --branch=develop https://github.com/mtm-aarhus/aaplus
cd aaplus
```



# Setting up Symfony

```
git clone git@github.com:mtm-aarhus/aaplus.git htdocs
composer install
```

# Create Database & update schema

```
php app/console doctrine:database:create
php app/console doctrine:schema:update --force
```

# Import data

1. Copy Excel files from Dropbox into the fixtures folder:

    ```
    mkdir -p src/AppBundle/DataFixtures/Data
    rm src/AppBundle/DataFixtures/Data/*.{csv,xlsm}
    cp -v ~/Dropbox*/Projekter/Aa+/Data/*.{csv,xlsm} src/AppBundle/DataFixtures/Data/
    ```

2. Load the fixtures (inside Vagrant box):

    ```
    php app/console doctrine:fixtures:load --purge-with-truncate
    ```

# Run unit tests

1. [Install PHPUnit](https://phpunit.de/manual/current/en/installation.html) if not already done

2. Import fixtures and generate unit test data (see above)

    ```
	rm src/AppBundle/DataFixtures/Data/fixtures/*
    DUMP_UNITTEST_DATA=php php app/console doctrine:fixtures:load --purge-with-truncate --no-interaction
    ```

3. Run unit tests

    ```
    phpunit -c app src/AppBundle/Tests/Entity
    ```
