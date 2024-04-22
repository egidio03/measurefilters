# MeasureFilter - App

This app is a simple website that manages filters of the various measures for the local ARPA's. It is possible to create, edit, delete and export filters to an excel file.

> This project has been realized for educational purposes only and not meant to be used for other scopes

## Installation

This app is developed using the PHP Framework Symfony in the LTS version(6.4) 
so you need to have PHP and Composer installed on your machine.


>https://symfony.com/download

>https://getcomposer.org/download/

Then you have to install Docker and Docker Compose to run the webserver and the database.

>https://docs.docker.com/get-docker/

You should install either the package 'make' to simplify the commands using the Makefile

>https://www.gnu.org/software/make/

After installing the requirements, you can clone the repository and install the dependencies with the following commands:

```bash
git clone https://github.com/<username>/MeasureFilter.git
cd MeasureFilter
make build && make up
```

**⚠️ Notice that with this configuration the webapp works only with the host: 'localhost' to make the website visible to the whole network you have to set manually the env like this:**

```bash
SERVER_NAME=<your_ip_address_or_host> make build && make up
```

## Usage

After the installation, you can access the website at the following address:

>http://localhost/login

You'll be redirected to the login page, you can use the following credentials to access the app:

- Username: user.default
- Password: 1234

# Attributions
- [Symfony](https://symfony.com/)
- [Symfony Docker](https://github.com/dunglas/symfony-docker/tree/main)
- [Docker](https://www.docker.com/)
- [Theme](https://startbootstrap.com/theme/sb-admin-2)

