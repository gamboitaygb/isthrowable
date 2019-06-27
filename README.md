# Personal Blog on symfony 

Is just a place for my memories but is free and you can use it

## Installation

First you need install [composer](https://getcomposer.org/download/).


## Usage

1-Clone the project or download from .zip

```
https://github.com/gamboitaygb/isthrowable.git
```

2-Install dependencies 
```
composer install 
```
3-Make sure to install MySQL and edit .env file
```
DATABASE_URL=mysql://dbuser:'dbpassword'@host_or_ip:3306/dbname
```
4-Execute the migrate command to create you database table from entity
```
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

That's all Folks



## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](https://choosealicense.com/licenses/mit/)

