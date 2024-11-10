# G1 News Client for PHP

This project provides an unofficial PHP client to retrieve news from the [G1 website](https://g1.globo.com/), a property of [Globo Comunicações e Participações S.A](https://grupoglobo.globo.com/). This API is created purely for educational purposes, with no commercial intent and no infringement on copyrights or related rights. The goal is to offer an easy, straightforward way to access prominent news from Brazil and around the world.

## Features

 - Fetch the latest news headlines and articles.

> [!NOTE]
> More features will be available soon.

## Installation

Install the package via Composer:

```bash
composer require adaiasmagdiel/g1-client
```

## Usage

To start using the G1 News Client, follow the steps below:

```php
<?php

// Autoload dependencies (make sure Composer's autoload is required)
require_once __DIR__ . "/vendor/autoload.php";

// Import the G1 Client package
use AdaiasMagdiel\G1\Client;

// Initialize the G1 Client
$api = new Client();

// Fetch the latest news headlines from all regions
$resAllRegions = $api->ultimas();
echo "Title of the first news item (all regions): " . $resAllRegions->news[0]->title . PHP_EOL;

// Fetch the latest news headlines from a specific state (e.g., Pará)
$resPara = $api->ultimas(estado: \AdaiasMagdiel\G1\Enum\Estado::Para);
echo "Title of the first news item (Pará): " . $resPara->news[0]->title . PHP_EOL;

// Fetch news from a specific page (e.g., page 2)
$resPage2 = $api->ultimas(page: 2);
echo "Title of the first news item (page 2): " . $resPage2->news[0]->title . PHP_EOL;
```
### Working with News Items

The `$res` object has a `news` attribute that contains an array of news items, each represented as an instance of the `News` class. The `News` class provides the following attributes for each news item:

- **url**: URL of the news article.
- **id**: Unique identifier of the news item.
- **feedId**: ID of the news feed.
- **type**: Type of news (e.g., article, video).
- **created**: Creation date of the news item.
- **modified**: Last modification date.
- **isPublishing**: Boolean indicating if the news item is currently being published.
- **images**: Array of available image sizes for the news item.
- **chapeu**: Not documented.
- **section**: Section of the website where the news item was published.
- **title**: Main title of the news item.
- **recommendationTitle**: Title suggested for recommendations.
- **summary**: Summary of the news item.
- **recommendationSummary**: Summary suggested for recommendations.

With these attributes, you can easily access detailed information about each news item.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for more details.
