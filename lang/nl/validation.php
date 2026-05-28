<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'accepted' => 'Het :attribute moet geaccepteerd worden.',
    'accepted_if' => 'Het :attribute moet geaccepteerd zijn wanneer :other gelijk is aan :value.',
    'active_url' => ':attribute is geen geldige URL.',
    'after' => ':attribute moet een datum na :date zijn.',
    'after_or_equal' => ':attribute moet een datum na of gelijk aan :date zijn.',
    'alpha' => ':attribute mag alleen letters bevatten.',
    'alpha_dash' => ':attribute mag alleen letters, nummers, underscores (_) en streepjes (-) bevatten.',
    'alpha_num' => ':attribute mag alleen letters en nummers bevatten.',
    'array' => ':attribute moet een array zijn.',
    'ascii' => ':attribute mag alleen alfanumerieke tekens en symbolen van één byte bevatten.',
    'before' => ':attribute moet een datum vóór :date zijn.',
    'before_or_equal' => ':attribute moet een datum vóór of gelijk aan :date zijn.',
    'between' => [
        'array' => ':attribute moet tussen :min en :max items hebben.',
        'file' => ':attribute moet tussen :min en :max kilobytes zijn.',
        'numeric' => ':attribute moet tussen :min en :max zijn.',
        'string' => ':attribute moet tussen :min en :max karakters zijn.',
    ],
    'boolean' => 'Het veld :attribute moet waar of niet waar zijn.',
    'can' => 'Het :attribute veld bevat een niet-toegestane waarde.',
    'confirmed' => 'De bevestiging van :attribute komt niet overeen.',
    'contains' => 'Het :attribute veld mist een verplichte waarde.',
    'current_password' => 'Wachtwoord is incorrect.',
    'date' => ':attribute moet een datum zijn.',
    'date_equals' => ':attribute moet een datum gelijk aan :date zijn.',
    'date_format' => ':attribute komt niet overeen met de gegeven formaat :format.',
    'decimal' => 'Het :attribute moet :decimal decimalen hebben.',
    'declined' => 'Het :attribute moet afgewezen worden.',
    'declined_if' => 'Het :attribute moet afgewezen zijn wanneer :other gelijk is aan :value.',
    'different' => ':attribute en :other moeten verschillend zijn.',
    'digits' => ':attribute moet bestaan uit :digits cijfers.',
    'digits_between' => ':attribute moet bestaan uit minimaal :min en maximaal :max cijfers.',
    'dimensions' => ':attribute heeft geen geldige afmetingen voor afbeeldingen.',
    'distinct' => ':attribute heeft een dubbele waarde.',
    'doesnt_end_with' => 'Het :attribute mag niet eindigen met één van het volgende: :values.',
    'doesnt_start_with' => 'Het :attribute mag niet beginnen met één van het volgende: :values.',
    'email' => ':attribute is geen geldig e-mailadres.',
    'ends_with' => ':attribute moet met één van de volgende waarden eindigen: :values.',
    'enum' => 'Geselecteerde :attribute is ongeldig.',
    'exists' => 'Geselecteerde :attribute is ongeldig.',
    'extensions' => 'Het :attribute veld moet een van de volgende extensies hebben: :values.',
    'file' => ':attribute moet een bestand zijn.',
    'filled' => ':attribute is verplicht.',
    'gt' => [
        'array' => ':attribute moet meer dan :value items hebben.',
        'file' => ':attribute moet groter zijn dan :value kilobytes.',
        'numeric' => ':attribute moet groter zijn dan :value.',
        'string' => ':attribute moet groter zijn dan :value karakters.',
    ],
    'gte' => [
        'array' => ':attribute moet :value items of meer hebben.',
        'file' => ':attribute moet groter of gelijk zijn aan :value kilobytes.',
        'numeric' => ':attribute moet groter of gelijk zijn aan :value.',
        'string' => ':attribute moet groter of gelijk zijn aan :value karakters.',
    ],
    'hex_color' => 'Het :attribute veld moet een geldige hexadecimale kleur zijn.',
    'image' => ':attribute moet een afbeelding zijn.',
    'in' => 'Geselecteerde :attribute is ongeldig.',
    'in_array' => ':attribute bestaat niet in :other.',
    'integer' => ':attribute moet een getal zijn.',
    'ip' => ':attribute moet een geldig IP-adres zijn.',
    'ipv4' => ':attribute moet een geldig IPv4-adres zijn.',
    'ipv6' => ':attribute moet een geldig IPv6-adres zijn.',
    'json' => ':attribute moet een geldige JSON-string zijn.',
    'list' => 'Het :attribute veld moet een lijst zijn.',
    'lowercase' => 'Het :attribute veld moet in kleine letters zijn.',
    'lt' => [
        'array' => ':attribute moet minder dan :value items hebben.',
        'file' => ':attribute moet kleiner zijn dan :value kilobytes.',
        'numeric' => ':attribute moet kleiner zijn dan :value.',
        'string' => ':attribute moet minder dan :value karakters zijn.',
    ],
    'lte' => [
        'array' => ':attribute mag niet meer dan :value items hebben.',
        'file' => ':attribute moet kleiner of gelijk zijn aan :value kilobytes.',
        'numeric' => ':attribute moet kleiner of gelijk zijn aan :value.',
        'string' => ':attribute moet kleiner of gelijk zijn aan :value karakters.',
    ],
    'mac_address' => 'Het :attribute moet een geldig MAC-adres zijn.',
    'max' => [
        'array' => ':attribute mag niet meer dan :max items hebben.',
        'file' => ':attribute mag niet meer dan :max kilobytes zijn.',
        'numeric' => ':attribute mag niet hoger dan :max zijn.',
        'string' => ':attribute mag niet uit meer dan :max karakters bestaan.',
    ],
    'max_digits' => 'Het :attribute mag niet meer dan :max cijfers bevatten.',
    'mimes' => ':attribute moet een bestand zijn van het bestandstype :values.',
    'mimetypes' => ':attribute moet een bestand zijn van het bestandstype :values.',
    'min' => [
        'array' => ':attribute moet minimaal :min items bevatten.',
        'file' => ':attribute moet minimaal :min kilobytes zijn.',
        'numeric' => ':attribute moet minimaal :min zijn.',
        'string' => ':attribute moet minimaal :min karakters zijn.',
    ],
    'min_digits' => 'Het :attribute moet minimaal :min cijfers bevatten.',
    'missing' => 'Het :attribute veld moet ontbreken.',
    'missing_if' => 'Het :attribute veld moet ontbreken wanneer :other gelijk is aan :value.',
    'missing_unless' => 'Het :attribute veld moet ontbreken tenzij :other gelijk is aan :value.',
    'missing_with' => 'Het :attribute veld moet ontbreken wanneer :values aanwezig is.',
    'missing_with_all' => 'Het :attribute veld moet ontbreken wanneer :values aanwezig zijn.',
    'multiple_of' => 'Het :attribute moet een veelvoud van :value zijn.',
    'not_in' => 'Het formaat van :attribute is ongeldig.',
    'not_regex' => 'Het formaat van :attribute is ongeldig.',
    'numeric' => ':attribute moet een nummer zijn.',
    'password' => [
        'letters' => 'Het :attribute veld moet ten minste één letter bevatten.',
        'mixed' => 'Het :attribute veld moet ten minste één hoofdletter en één kleine letter bevatten.',
        'numbers' => 'Het :attribute veld moet ten minste één cijfer bevatten.',
        'symbols' => 'Het :attribute veld moet ten minste één symbool bevatten.',
        'uncompromised' => 'Het opgegeven :attribute is verschenen in een datalek. Kies een ander :attribute.',
    ],
    'present' => ':attribute moet bestaan.',
    'present_if' => 'Het :attribute veld moet aanwezig zijn wanneer :other gelijk is aan :value.',
    'present_unless' => 'Het :attribute veld moet aanwezig zijn tenzij :other gelijk is aan :value.',
    'present_with' => 'Het :attribute veld moet aanwezig zijn wanneer :values aanwezig is.',
    'present_with_all' => 'Het :attribute veld moet aanwezig zijn wanneer :values aanwezig zijn.',
    'prohibited' => ':attribute veld is verboden.',
    'prohibited_if' => ':attribute veld is verboden indien :other gelijk is aan :value.',
    'prohibited_if_accepted' => 'Het :attribute veld is verboden wanneer :other is geaccepteerd.',
    'prohibited_if_declined' => 'Het :attribute veld is verboden wanneer :other is afgewezen.',
    'prohibited_unless' => ':attribute veld is verboden tenzij :other zich in :values bevindt.',
    'prohibits' => 'Het veld :attribute verbiedt dat :other aanwezig is.',
    'regex' => ':attribute formaat is ongeldig.',
    'required' => ':attribute is verplicht.',
    'required_array_keys' => 'Het :attribute veld moet waarden bevatten voor: :values.',
    'required_if' => ':attribute is verplicht indien :other gelijk is aan :value.',
    'required_if_accepted' => 'Het :attribute veld is verplicht wanneer :other is geaccepteerd.',
    'required_if_declined' => 'Het :attribute veld is verplicht wanneer :other is afgewezen.',
    'required_unless' => ':attribute is verplicht tenzij :other in :values voorkomt.',
    'required_with' => ':attribute is verplicht in combinatie met :values.',
    'required_with_all' => ':attribute is verplicht in combinatie met :values.',
    'required_without' => ':attribute is verplicht als :values niet ingevuld is.',
    'required_without_all' => ':attribute is verplicht als :values niet ingevuld zijn.',
    'same' => ':attribute en :other moeten overeenkomen.',
    'size' => [
        'array' => ':attribute moet :size items bevatten.',
        'file' => ':attribute moet :size kilobyte zijn.',
        'numeric' => ':attribute moet :size zijn.',
        'string' => ':attribute moet :size karakters zijn.',
    ],
    'starts_with' => ':attribute moet starten met een van de volgende: :values.',
    'string' => ':attribute moet een tekst zijn.',
    'timezone' => ':attribute moet een geldige tijdzone zijn.',
    'unique' => ':attribute is al in gebruik.',
    'uploaded' => 'Het uploaden van :attribute is mislukt.',
    'uppercase' => 'Het :attribute veld moet in hoofdletters zijn.',
    'url' => ':attribute formaat is ongeldig.',
    'ulid' => 'Het :attribute moet een geldige ULID zijn.',
    'uuid' => ':attribute moet een geldige UUID zijn.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [],

];
