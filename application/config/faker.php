<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* 
 * These provider were formatted from PHP Faker at https://github.com/fzaninotto/Faker
 * Some formatter have been removed because their functionalities are too similar
 * Others have been merged with others.
*/
$config['providers'] = [
    [
        'name' => 'Basic',
        'text' => 'Generates basic data elements',
        'formatters' => [
            [
                'name' => 'Digit',
                'func' => 'randomDigitNotNull',
                'example' => 7
            ],
            [
                'name' => 'Float',
                'func' => 'randomFloat',
                'params' => [
                    ['name' => 'Max Decimals', 'type' => "number"],
                    ['name' => 'Min Digits', 'type' => "number"],
                    ['name' => 'Max Digits', 'type' => "number"],
                ],
                'example' => 48.8932
            ],
            [
                'name' => 'Number',
                'func' => 'numberBetween',
                'params' => [
                    ['name' => 'Min Digits', 'type' => "number"],
                    ['name' => 'Max Digits', 'type' => "number"],
                ],
                'example' => 8567
            ],
            [
                'name' => 'Letter',
                'func' => 'randomLetter',
                'example' => 'b'
            ],
            [
                'name' => 'Boolean',
                'func' => 'boolean',
                'params' => [
                    [ 'name' => 'chance of getting true (in percentage)', 'type' => 'number'],
                ],
                'example' => 'true or false'
            ],
            [
                'name' => 'Regex',
                'func' => 'regexify',
                'example' => '[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4} creates sm0@y8k96a.ej'
            ],
        ],
    ],
    
    [
        'name' => 'Text',
        'text' => 'Generates random text elements',
        'formatters' => [
            [
                'name' => 'Word',
                'func' => 'word',
                'example' => 'porro',
            ],
            [
                'name' => 'Sentences',
                'func' => 'sentence',
                'params' => [
                    ['name' => 'Number of words', 'type' => "text"],
                ],
                'example' => 'Sit vitae voluptas sint non voluptates.'

            ],
            [
                'name' => 'Paragraphs',
                'func' => 'paragraph',
                'params' => [
                    ['name' => 'Number of sentences', 'type' => "text"],
                ],
                'example' => 'Ut ab voluptas sed a nam. Sint autem inventore aut officia aut aut blanditiis. Ducimus eos odit amet et est ut eum.'

            ],
            [
                'name' => 'Text',
                'func' => 'text',
                'params' => [
                    ['name' => 'Number of characters e.g. 200', 'type' => "number"],
                ],
                'example' => 'Fuga totam reiciendis qui architecto fugiat nemo. Consequatur recusandae qui cupiditate eos quod.'

            ],
            [
                'name' => 'Real Text',
                'func' => 'text',
                'params' => [
                    ['name' => 'Number of characters e.g. 200', 'type' => "number"],
                ],
                'example' => "And yet I wish you could manage it?) 'And what are they made of?' Alice asked in a shrill, passionate voice. 'Would YOU like cats if you were never even spoke to Time!' 'Perhaps not,' Alice replied."
            ],
        ]
    ],

    [
        'name' => 'Person',
        'text' => 'Generates information about a person',
        'formatters' => [
            [
                'name' => 'Name',
                'func' => 'name',
                'params' => [
                    ['name' => 'Male', 'type' => "radio", 'value' => 'male', 'category' => 'name'],
                    ['name' => 'Female', 'type' => "radio", 'value' => 'female', 'category' => 'name'],
                ],
                'example' => 'Dr. Zane Stroman'
            ],
            [
                'name' => 'First Name',
                'func' => 'firstName',
                'params' => [
                    ['name' => 'Male', 'type' => "radio", 'value' => 'male', 'category' => 'fname'],
                    ['name' => 'Female', 'type' => "radio", 'value' => 'female', 'category' => 'fname'],
                ],
                'example' => 'Rachel'
            ],
            [
                'name' => 'Title',
                'func' => 'title',
                'params' => [
                    ['name' => 'Male', 'type' => "radio", 'value' => 'male', 'category' => 'title'],
                    ['name' => 'Female', 'type' => "radio", 'value' => 'female', 'category' => 'title'],
                ],
                'example' => 'Mr.'
            ],
            [
                'name' => 'Last Name',
                'func' => 'lastName',
                'example' => 'Zulauf'
            ],
            [
                'name' => 'Suffix',
                'func' => 'suffix',
                'example' => 'Jr.'
            ],
        ],
    ],

    [
        'name' => 'Contacts',
        'text' => 'Generates a person or company contact or location data',
        'formatters' => [
            [
                'name' => 'Toll free phone number',
                'func' => 'tollFreePhoneNumber',
                'example' => '(888) 937-7238'
            ],
            [
                'name' => 'phone number with code',
                'func' => 'e164PhoneNumber',
                'example' => '+27113456789'
            ],
            [
                'name' => 'Catch phrase',
                'func' => 'catchPhrase',
                'example' => 'Monitored regional contingency'
            ],
            [
                'name' => 'Slogan',
                'func' => 'bs',
                'example' => 'e-enable robust architectures'
            ],
            [
                'name' => 'Company',
                'func' => 'company',
                'example' => 'Bogan-Treutel'
            ],
            [
                'name' => 'Job title',
                'func' => 'jobTitle',
                'example' => 'Cashier'
            ],
            [
                'name' => 'Address',
                'func' => 'address',
                'example' => '8888 Cummings Vista Apt. 101, Susanbury, NY 95473'
            ],
            [
                'name' => 'Secondary Address',
                'func' => 'secondaryAddress',
                'example' => 'Suite 961'
            ],
            [
                'name' => 'Country',
                'func' => 'country',
                'example' => 'Falkland Islands (Malvinas)'
            ],
            [
                'name' => 'State',
                'func' => 'state',
                'example' => 'NewMexico'
            ],
            [
                'name' => 'State Abbreviation',
                'func' => 'stateAbbr',
                'example' => 'OH'
            ],
            [
                'name' => 'City',
                'func' => 'city',
                'example' => 'West Judge'
            ],
            [
                'name' => 'City Prefix',
                'func' => 'cityPrefix',
                'example' => 'Lake'
            ],
            [
                'name' => 'City suffix',
                'func' => 'citySuffix',
                'example' => 'borough'
            ],
            [
                'name' => 'Street suffix',
                'func' => 'streetSuffix',
                'example' => 'Keys'
            ],
            [
                'name' => 'Building number',
                'func' => 'buildingNumber',
                'example' => '484'
            ],
            [
                'name' => 'Street name',
                'func' => 'streetName',
                'example' => 'Keegan Trail'
            ],
            [
                'name' => 'Street address',
                'func' => 'streetAddress',
                'example' => '439 Karley Loaf Suite 897'
            ],
            [
                'name' => 'Post code',
                'func' => 'postcode',
                'example' => '17916'
            ],
            [
                'name' => 'Latitude',
                'func' => 'latitude',
                'params' => [
                    ['name' => 'Min e.g. -90', 'type' => 'number'],
                    ['name' => 'Max e.g. 90', 'type' => 'number'],
                ],
                'example' => 77.147489
            ],
            [
                'name' => 'Longitude',
                'func' => 'longitude',
                'params' => [
                    ['name' => 'Min e.g. -180', 'type' => 'number'],
                    ['name' => 'Max e.g. 180', 'type' => 'number'],
                ],
                'example' => 86.211205
            ],
        ]
    ],

    [
        'name' => 'Date/Time',
        'text' => 'Generates date time information',
        'formatters' => [
            [
                'name' => 'Unix time',
                'func' => 'unixTime',
                'params' => [
                    ['name' => 'Max date', 'type' => "text"],
                ],
                'example' => 58781813
            ],
            [
                'name' => 'Date time',
                'func' => 'dateTime',
                'params' => [
                    ['name' => 'Max date', 'type' => "text"],
                ],
                'example' => "DateTime('2008-04-25 08:37:17', 'UTC')"
            ],
            [
                'name' => 'Period between dates',
                'func' => "dateTimeBetween",
                'params' => [
                    ['name' => 'Start date e.g. -30 years', 'type' => "text"],
                    ['name' => 'End date e.g. now', 'type' => "text"],
                ],
                'example' => "DateTime('2003-03-15 02:00:49', 'Africa/Lagos')"
            ],
            [
                'name' => 'Date in intervals',
                'func' => "dateTimeInInterval",
                'params' => [
                    ['name' => 'Start date e.g. -30 years', 'type' => "text"],
                    ['name' => 'Interval e.g. + 5 days', 'type' => "text"],
                ],
                'example' => "DateTime('2003-03-15 02:00:49', 'Antartica/Vostok')"
            ],
            [
                'name' => 'Century',
                'func' => 'century',
                'example' => 'VI'
            ],
            [
                'name' => 'Timezone',
                'func' => 'timezone',
                'example' => 'Europe/Paris'
            ],
        ],
    ],
    // Methods accepting a $timezone argument default to date_default_timezone_get(). You can pass a custom timezone string to each method, or define a custom timezone for all time methods at once using $faker::setDefaultTimezone($timezone).

    [
        'name' => 'Internet',
        'text' => 'Generates internet data like emails, user agents, IP addresses',
        'formatters' => [
            [
                'name' => 'Email',
                'func' => 'email',
                'example' => 'tkshlerin@collins.com'
            ],
            [
                'name' => 'Safe email',
                'func' => 'safeEmail',
                'example' => 'king.alford@example.org'
            ],
            [
                'name' => 'Free email',
                'func' => 'freeEmail',
                'example' => 'bradley72@gmail.com'
            ],
            [
                'name' => 'Company email',
                'func' => 'companyEmail',
                'example' => 'russel.durward@mcdermott.org'
            ],
            [
                'name' => 'Free email domain',
                'func' => 'freeEmailDomain',
                'example' => 'yahoo.com'
            ],
            [
                'name' => 'Safe email domain',
                'func' => 'safeEmailDomain',
                'example' => 'example.org'
            ],
            [
                'name' => 'Username',
                'func' => 'userName',
                'example' => 'wade55'
            ],
            [
                'name' => 'Password',
                'func' => 'password',
                'example' => 'k&|X+a45*2['
            ],
            [
                'name' => 'Domain name',
                'func' => 'domainName',
                'example' => 'wolffdeckow.net'
            ],
            [
                'name' => 'Domain word',
                'func' => 'domainWord',
                'example' => 'feeney'
            ],
            [
                'name' => 'tld',
                'func' => 'tld',
                'example' => 'biz'
            ],
            [
                'name' => 'Url',
                'func' => 'url',
                'example' => 'http://www.skilesdonnelly.biz/aut-accusantium-ut-architecto-sit-et.html'
            ],
            [
                'func' => 'Slug',
                'name' => 'slug',
                'example' => 'aut-repellat-commodi-vel-itaque-nihil-id-saepe-nostrum'
            ],
            [
                'name' => 'ipv4',
                'func' => 'ipv4',
                'example' => '109.133.32.252'
            ],
            [
                'name' => 'localIpv4',
                'func' => 'localIpv4',
                'example' => '10.242.58.8'
            ],
            [
                'name' => 'ipv6',
                'func' => 'ipv6',
                'example' => '8e65:933d:22ee:a232:f1c1:2741:1f10:117c'
            ],
            [
                'name' => 'mac address',
                'func' => 'macAddress',
                'example' => '43:85:B7:08:10:CA'
            ],
            [
                'name' => 'uuid',
                'func' => 'uuid',
                'example' => '7e57d004-2b97-0e7a-b45f-5387367791cd'
            ],
            [
                'name' => 'userAgent',
                'func' => 'userAgent',
                'example' => 'Mozilla/5.0 (Windows CE) AppleWebKit/5350 (KHTML, like Gecko) Chrome/13.0.888.0 Safari/5350'
            ],
            [
                'name' => 'chrome',
                'func' => 'chrome',
                'example' => 'Mozilla/5.0 (Macintosh; PPC Mac OS X 10_6_5) AppleWebKit/5312 (KHTML, like Gecko) Chrome/14.0.894.0 Safari/5312'
            ],
            [
                'name' => 'firefox',
                'func' => 'firefox',
                'example' => 'Mozilla/5.0 (X11; Linuxi686; rv:7.0) Gecko/20101231 Firefox/3.6'
            ],
            [
                'name' => 'safari',
                'func' => 'safari',
                'example' => 'Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_7_1 rv:3.0; en-US) AppleWebKit/534.11.3 (KHTML, like Gecko) Version/4.0 Safari/534.11.3'
            ],
            [
                'name' => 'opera',
                'func' => 'opera',
                'example' => 'Opera/8.25 (Windows NT 5.1; en-US) Presto/2.9.188 Version/10.00'
            ],
            [
                'name' => 'internetExplorer',
                'func' => 'internetExplorer',
                'example' => 'Mozilla/5.0 (compatible; MSIE 7.0; Windows 98; Win 9x 4.90; Trident/3.0)'//Generate HTML document which is no more than 2 levels deep, and no more than 3 elements wide at any level.
            ],
            [
                'name' => 'HTML document',
                'func' => 'randomHtml',
                'params' => [
                    ['name' => 'Number of DOM levels', 'type' => "number"],
                    ['name' => 'Min elements per level', 'type' => "number"],
                ],
                'example' => '<html><head><title>Aut illo dolorem et accusantium eum.</title></head><body><form action="example.com" method="POST"><label for="username">sequi</label><input type="text" id="username"><label for="password">et</label><input type="password" id="password"></form><b>Id aut saepe non mollitia voluptas voluptas.</b><table><thead><tr><tr>Non consequatur.</tr><tr>Incidunt est.</tr><tr>Aut voluptatem.</tr><tr>Officia voluptas rerum quo.</tr><tr>Asperiores similique.</tr></tr></thead><tbody><tr><td>Sapiente dolorum dolorem sint laboriosam commodi qui.</td><td>Commodi nihil nesciunt eveniet quo repudiandae.</td><td>Voluptates explicabo numquam distinctio necessitatibus repellat.</td><td>Provident ut doloremque nam eum modi aspernatur.</td><td>Iusto inventore.</td></tr><tr><td>Animi nihil ratione id mollitia libero ipsa quia tempore.</td><td>Velit est officia et aut tenetur dolorem sed mollitia expedita.</td><td>Modi modi repudiandae pariatur voluptas rerum ea incidunt non molestiae eligendi eos deleniti.</td><td>Exercitationem voluptatibus dolor est iste quod molestiae.</td><td>Quia reiciendis.</td></tr><tr><td>Inventore impedit exercitationem voluptatibus rerum cupiditate.</td><td>Qui.</td><td>Aliquam.</td><td>Autem nihil aut et.</td><td>Dolor ut quia error.</td></tr><tr><td>Enim facilis iusto earum et minus rerum assumenda quis quia.</td><td>Reprehenderit ut sapiente occaecati voluptatum dolor voluptatem vitae qui velit.</td><td>Quod fugiat non.</td><td>Sunt nobis totam mollitia sed nesciunt est deleniti cumque.</td><td>Repudiandae quo.</td></tr><tr><td>Modi dicta libero quisquam doloremque qui autem.</td><td>Voluptatem aliquid saepe laudantium facere eos sunt dolor.</td><td>Est eos quis laboriosam officia expedita repellendus quia natus.</td><td>Et neque delectus quod fugit enim repudiandae qui.</td><td>Fugit soluta sit facilis facere repellat culpa magni voluptatem maiores tempora.</td></tr><tr><td>Enim dolores doloremque.</td><td>Assumenda voluptatem eum perferendis exercitationem.</td><td>Quasi in fugit deserunt ea perferendis sunt nemo consequatur dolorum soluta.</td><td>Maxime repellat qui numquam voluptatem est modi.</td><td>Alias rerum rerum hic hic eveniet.</td></tr><tr><td>Tempore voluptatem.</td><td>Eaque.</td><td>Et sit quas fugit iusto.</td><td>Nemo nihil rerum dignissimos et esse.</td><td>Repudiandae ipsum numquam.</td></tr><tr><td>Nemo sunt quia.</td><td>Sint tempore est neque ducimus harum sed.</td><td>Dicta placeat atque libero nihil.</td><td>Et qui aperiam temporibus facilis eum.</td><td>Ut dolores qui enim et maiores nesciunt.</td></tr><tr><td>Dolorum totam sint debitis saepe laborum.</td><td>Quidem corrupti ea.</td><td>Cum voluptas quod.</td><td>Possimus consequatur quasi dolorem ut et.</td><td>Et velit non hic labore repudiandae quis.</td></tr></tbody></table></body></html>'
            ],
        ],
    ],

    [
        'name' => 'Payment',
        'text' => 'Generates information on Payment details',
        'formatters' => [
            [
                'name' => 'Credit card type',
                'func' => 'creditCardType',
                'example' => 'MasterCard'
            ],
            [
                'name' => 'Credit card number',
                'func' => 'creditCardNumber',
                'example' => '4485480221084675'
            ],
            [
                'name' => 'Credit card expiration date',
                'func' => 'creditCardExpirationDateString',
                'example' => '04/23'
            ],
            [
                'name' => 'Credit card details',
                'func' => 'creditCardDetails',
                'example' => serialize(array('MasterCard', '4485480221084675', 'Aleksander Nowak', '04/23'))
            ],
            [
                'name' => 'iban',
                'func' => 'iban',
                'example' => 'IT31A8497112740YZ575DJ28BP4'
            ],
            [
                'name' => 'Swift Bic Number',
                'func' => 'swiftBicNumber',
                'example' => 'RZTIAT22263'
            ],
            [
                'name' => 'Barcode ean13',
                'func' => 'ean13',
                'example' => '4006381333931' // Barcode type
            ],
            [
                'name' => 'Barcode ean8',
                'func' => 'ean8',
                'example' => '73513537' // Barcode type
            ],
            [
                'name' => 'Barcode isbn13',
                'func' => 'isbn13',
                'example' => '9790404436093' // Barcode type
            ],
            [
                'name' => 'Barcode isbn10',
                'func' => 'isbn10',
                'example' => '4881416324' // Barcode type
            ],
        ],
    ],

    [
        'name' => 'Color',
        'text' => 'Generates random colors',
        'formatters' => [
            [
                'name' => 'Hex color',
                'func' => 'hexcolor',
                'example' => '#fa3cc2'
            ],
            [
                'name' => 'RGB color',
                'func' => 'rgbcolor',
                'example' => '0,255,122'
            ],
            [
                'name' => 'RGB CSS color',
                'func' => 'rgbCssColor',
                'example' => 'rgb(0,255,122)'
            ],
            [
                'name' => 'Color name',
                'func' => 'safeColorName',
                'example' => 'fuchsia'
            ],
        ],
    ],

    [
        'name' => 'Files',
        'text' => 'Generates files and images',
        'formatters' => [
            [
                'name' => 'File extension',
                'func' => 'fileExtension',
                'example' => 'avi'
            ],
            [
                'name' => 'mime type',
                'func' => 'mimeType',
                'example' => 'video/x-msvideo'
            ],
            [
                'name' => 'Copy any file',
                'func' => 'file',
                'params' => [
                    ['name' => 'source directory', 'type' => "text"],
                    ['name' => 'target directory', 'type' => "text"],
                ],
                'example' => '/path/to/targetDir/13b73edae8443990be1aa8f1a483bc27.jpg',
            ],
            [
                'name' => 'Image URL',
                'func' => 'imageUrl',
                'params' => [
                    ['name' => 'Width', 'type' => "number"],
                    ['name' => 'Height', 'type' => "number"],
                    ['name' => 'Category e.g. cats', 'type' => "text"],
                    ['name' => 'Randomize image', 'type' => "checkbox", 'value' => "true", "category" => 'random'],
                    ['name' => 'Image text', 'type' => "text"],
                    ['name' => 'Apply grayscale', 'type' => "checkbox", 'value' => "true", "category" => 'grayscale'],
                ],
                'example' => 'http://lorempixel.com/grey/800/400/cats/Faker/',
            ],
            [
                'name' => 'Image',
                'func' => 'image',
                'params' => [
                    ['name' => 'Width', 'type' => "number"],
                    ['name' => 'Height', 'type' => "number"],
                    ['name' => 'Target directory', 'type' => "text"],
                    ['name' => 'Include path name', 'type' => "checkbox", 'value' => "true", "category" => 'path'],
                    ['name' => 'Randomize image', 'type' => "checkbox", 'value' => "true", "category" => 'random'],
                    ['name' => 'Image text', 'type' => "text"],
                ],
            ],
        ],
    ],

    [
        'name' => 'Miscellaneous',
        'text' => 'Generates other data elements',
        'formatters' =>  [
            [
                'name' => 'md5',
                'func' => 'md5',
                'example' => 'de99a620c50f2990e87144735cd357e7'
            ],
            [
                'name' => 'sha1',
                'func' => 'sha1',
                'example' => 'f08e7f04ca1a413807ebc47551a40a20a0b4de5c'
            ],
            [
                'name' => 'sha256',
                'func' => 'sha256',
                'example' => '0061e4c60dac5c1d82db0135a42e00c89ae3a333e7c26485321f24348c7e98a5'
            ],
            [
                'name' => 'locale',
                'func' => 'locale',
                'example' => 'en_UK'
            ],
            [
                'name' => 'Country code',
                'func' => 'countryCode',
                'example' => 'UK'
            ],
            [
                'name' => 'Language code',
                'func' => 'languageCode' ,
                'example' => 'en'
            ],
            [
                'name' => 'Currency ocde',
                'func' => 'currencyCode',
                'example' => 'EUR'
            ],
            [
                'name' => 'emoji',
                'func' => 'emoji',
                'example' => '😁'
            ],
        ],
    ],
];