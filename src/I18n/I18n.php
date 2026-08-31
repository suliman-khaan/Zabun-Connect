<?php

namespace ZabunConnect\I18n;

defined( 'ABSPATH' ) || exit;

class I18n {

    /**
     * Supported languages.
     */
    public const SUPPORTED_LANGUAGES = [
        'nl' => 'Nederlands (Dutch)',
        'fr' => 'Français (French)',
        'en' => 'English',
    ];

    /**
     * Default language.
     */
    public const DEFAULT_LANGUAGE = 'nl';

    /**
     * Get active configured language code ('nl', 'fr', or 'en').
     *
     * @return string
     */
    public static function get_current_language(): string {
        // 1. Check URL query parameter override if present (e.g. ?lang=nl or ?zabun_lang=fr)
        if ( ! empty( $_GET['zabun_lang'] ) && isset( self::SUPPORTED_LANGUAGES[ sanitize_key( $_GET['zabun_lang'] ) ] ) ) {
            return sanitize_key( $_GET['zabun_lang'] );
        }
        if ( ! empty( $_GET['lang'] ) && isset( self::SUPPORTED_LANGUAGES[ sanitize_key( $_GET['lang'] ) ] ) ) {
            return sanitize_key( $_GET['lang'] );
        }

        // 2. Check saved plugin setting
        $saved = (string) get_option( 'zabun_connect_language', '' );
        if ( ! empty( $saved ) && isset( self::SUPPORTED_LANGUAGES[ $saved ] ) ) {
            return $saved;
        }

        // 3. Fallback to site locale if supported
        if ( function_exists( 'get_locale' ) ) {
            $site_lang = strtolower( substr( get_locale(), 0, 2 ) );
            if ( isset( self::SUPPORTED_LANGUAGES[ $site_lang ] ) ) {
                return $site_lang;
            }
        }

        return self::DEFAULT_LANGUAGE;
    }

    /**
     * Get all supported languages.
     *
     * @return array
     */
    public static function get_supported_languages(): array {
        return self::SUPPORTED_LANGUAGES;
    }

    /**
     * Extract string from multilingual array, object, or string.
     * Handles API structures like:
     *   "title": {
     *       "en": "1,300m2 warehouse with loading dock and office",
     *       "fr": "Entrepôt de 1 300 m2 avec quai de chargement et bureau",
     *       "nl": "1.300m2 magazijn met Laadkade en kantoor"
     *   }
     *
     * @param mixed $val Value to extract.
     * @param string|null $lang Optional explicit language code ('nl', 'fr', 'en').
     * @param string $default Fallback if empty.
     * @return string
     */
    public static function extract( $val, ?string $lang = null, string $default = '' ): string {
        if ( is_string( $val ) ) {
            return trim( $val );
        }

        if ( is_object( $val ) ) {
            $val = (array) $val;
        }

        if ( is_array( $val ) ) {
            $active_lang = ! empty( $lang ) ? strtolower( trim( $lang ) ) : self::get_current_language();

            // 1. Exact match for active language
            if ( isset( $val[ $active_lang ] ) && is_string( $val[ $active_lang ] ) && '' !== trim( $val[ $active_lang ] ) ) {
                return trim( $val[ $active_lang ] );
            }

            // 2. Language fallbacks in sensible order
            $fallback_order = [ 'nl', 'fr', 'en' ];
            foreach ( $fallback_order as $fb ) {
                if ( isset( $val[ $fb ] ) && is_string( $val[ $fb ] ) && '' !== trim( $val[ $fb ] ) ) {
                    return trim( $val[ $fb ] );
                }
            }

            // 3. First non-empty string value
            foreach ( $val as $k => $v ) {
                if ( is_string( $v ) && '' !== trim( $v ) ) {
                    return trim( $v );
                }
            }
        }

        return $default;
    }

    /**
     * Static UI Translation Dictionary.
     *
     * @return array
     */
    public static function get_dictionary(): array {
        return [
            // Statuses
            'status_for_sale' => [
                'nl' => 'Te koop',
                'fr' => 'À vendre',
                'en' => 'For sale',
            ],
            'status_for_rent' => [
                'nl' => 'Te huur',
                'fr' => 'À louer',
                'en' => 'For rent',
            ],
            'status_sold' => [
                'nl' => 'Verkocht',
                'fr' => 'Vendu',
                'en' => 'Sold',
            ],
            'status_rented' => [
                'nl' => 'Verhuurd',
                'fr' => 'Loué',
                'en' => 'Rented',
            ],
            'all_statuses' => [
                'nl' => 'Alle statussen',
                'fr' => 'Tous les statuts',
                'en' => 'All Statuses',
            ],

            // Pricing & Units
            'price_on_request' => [
                'nl' => 'Prijs op aanvraag',
                'fr' => 'Prix sur demande',
                'en' => 'Price on request',
            ],
            'unit_month' => [
                'nl' => 'maand',
                'fr' => 'mois',
                'en' => 'month',
            ],
            'unit_year' => [
                'nl' => 'jaar',
                'fr' => 'an',
                'en' => 'year',
            ],
            'unit_sqm' => [
                'nl' => 'm²',
                'fr' => 'm²',
                'en' => 'm²',
            ],

            // Property Card Facts
            'beds' => [
                'nl' => 'Slpks',
                'fr' => 'Chambres',
                'en' => 'Beds',
            ],
            'baths' => [
                'nl' => 'Badkamers',
                'fr' => 'Salles de bain',
                'en' => 'Baths',
            ],
            'no_image_available' => [
                'nl' => 'Geen afbeelding beschikbaar',
                'fr' => 'Pas d\'image disponible',
                'en' => 'No Image Available',
            ],
            'no_properties_found' => [
                'nl' => 'Geen panden gevonden die voldoen aan uw criteria.',
                'fr' => 'Aucun bien immobilier ne correspond à vos critères.',
                'en' => 'No property listings found matching your criteria.',
            ],
            'showing_listings_count' => [
                'nl' => 'Toont %1$s–%2$s van %3$s panden',
                'fr' => 'Affichage de %1$s–%2$s sur %3$s biens',
                'en' => 'Showing %1$s–%2$s of %3$s listings',
            ],
            'page' => [
                'nl' => 'Pagina',
                'fr' => 'Page',
                'en' => 'Page',
            ],
            'of' => [
                'nl' => 'van',
                'fr' => 'sur',
                'en' => 'of',
            ],
            'go' => [
                'nl' => 'Ga',
                'fr' => 'Aller',
                'en' => 'Go',
            ],
            'go_to_page' => [
                'nl' => 'Ga naar pagina',
                'fr' => 'Aller à la page',
                'en' => 'Go to page',
            ],

            // Detail Page
            'property_not_found' => [
                'nl' => 'Pand niet gevonden of ongeldig ID opgegeven.',
                'fr' => 'Bien non trouvé ou identifiant invalide.',
                'en' => 'Property not found or invalid ID specified.',
            ],
            'view_all_photos' => [
                'nl' => 'Bekijk alle foto\'s',
                'fr' => 'Voir toutes les photos',
                'en' => 'View all photos',
            ],
            'photos' => [
                'nl' => 'foto\'s',
                'fr' => 'photos',
                'en' => 'photos',
            ],
            'description' => [
                'nl' => 'Beschrijving',
                'fr' => 'Description',
                'en' => 'Description',
            ],
            'key_details' => [
                'nl' => 'Belangrijkste details',
                'fr' => 'Détails clés',
                'en' => 'Key details',
            ],
            'reference' => [
                'nl' => 'Referentie',
                'fr' => 'Référence',
                'en' => 'Reference',
            ],
            'property_type' => [
                'nl' => 'Type eigendom',
                'fr' => 'Type de bien',
                'en' => 'Property type',
            ],
            'status' => [
                'nl' => 'Status',
                'fr' => 'Statut',
                'en' => 'Status',
            ],
            'year_built' => [
                'nl' => 'Bouwjaar',
                'fr' => 'Année de construction',
                'en' => 'Year built',
            ],
            'habitable_area' => [
                'nl' => 'Bewoonbare oppervlakte',
                'fr' => 'Surface habitable',
                'en' => 'Habitable area',
            ],
            'plot_size' => [
                'nl' => 'Grondoppervlakte',
                'fr' => 'Superficie du terrain',
                'en' => 'Plot size',
            ],
            'epc_label' => [
                'nl' => 'EPC-label',
                'fr' => 'Label PEB',
                'en' => 'EPC label',
            ],
            'availability' => [
                'nl' => 'Beschikbaarheid',
                'fr' => 'Disponibilité',
                'en' => 'Availability',
            ],
            'features' => [
                'nl' => 'Kenmerken',
                'fr' => 'Caractéristiques',
                'en' => 'Features',
            ],
            'listing_agent' => [
                'nl' => 'Vastgoedmakelaar',
                'fr' => 'Agent immobilier',
                'en' => 'Listing Agent',
            ],
            'request_viewing' => [
                'nl' => 'Vraag een bezichtiging aan',
                'fr' => 'Demander une visite',
                'en' => 'Request a viewing',
            ],
            'download_brochure' => [
                'nl' => 'Download brochure',
                'fr' => 'Télécharger la brochure',
                'en' => 'Download brochure',
            ],
            'synced_notice' => [
                'nl' => 'Ref. %s · gesynchroniseerd via Zabun',
                'fr' => 'Réf. %s · synchronisé depuis Zabun',
                'en' => 'Ref. %s · synced from Zabun',
            ],
            'close' => [
                'nl' => 'Sluiten',
                'fr' => 'Fermer',
                'en' => 'Close',
            ],
            'previous' => [
                'nl' => 'Vorige',
                'fr' => 'Précédent',
                'en' => 'Previous',
            ],
            'next' => [
                'nl' => 'Volgende',
                'fr' => 'Suivant',
                'en' => 'Next',
            ],
            'photo_n' => [
                'nl' => 'Foto %d',
                'fr' => 'Photo %d',
                'en' => 'Photo %d',
            ],

            // Search Bar & Filter Form
            'keyword_or_location' => [
                'nl' => 'Trefwoord of locatie',
                'fr' => 'Mot-clé ou localisation',
                'en' => 'Keyword or location',
            ],
            'search_placeholder' => [
                'nl' => 'Adres, gemeente, referentie...',
                'fr' => 'Adresse, ville, référence...',
                'en' => 'Address, city, reference...',
            ],
            'city' => [
                'nl' => 'Gemeente',
                'fr' => 'Ville',
                'en' => 'City',
            ],
            'all_cities' => [
                'nl' => 'Alle gemeenten',
                'fr' => 'Toutes les villes',
                'en' => 'All cities',
            ],
            'all_types' => [
                'nl' => 'Alle types',
                'fr' => 'Tous les types',
                'en' => 'All types',
            ],
            'max_price' => [
                'nl' => 'Max. prijs',
                'fr' => 'Prix max.',
                'en' => 'Max price',
            ],
            'more_filters' => [
                'nl' => 'Meer filters',
                'fr' => 'Plus de filtres',
                'en' => 'More filters',
            ],
            'search' => [
                'nl' => 'Zoeken',
                'fr' => 'Rechercher',
                'en' => 'Search',
            ],
            'min_max_price' => [
                'nl' => 'Min. en max. prijs',
                'fr' => 'Prix min. et max.',
                'en' => 'Min & max price',
            ],
            'bedrooms' => [
                'nl' => 'Slaapkamers',
                'fr' => 'Chambres',
                'en' => 'Bedrooms',
            ],
            'bathrooms' => [
                'nl' => 'Badkamers',
                'fr' => 'Salles de bain',
                'en' => 'Bathrooms',
            ],
            'surface_sqm' => [
                'nl' => 'Oppervlakte (m²)',
                'fr' => 'Surface (m²)',
                'en' => 'Surface (m²)',
            ],
            'min' => [
                'nl' => 'Min',
                'fr' => 'Min',
                'en' => 'Min',
            ],
            'max' => [
                'nl' => 'Max',
                'fr' => 'Max',
                'en' => 'Max',
            ],
            'min_with_unit' => [
                'nl' => 'Min (%d m²)',
                'fr' => 'Min (%d m²)',
                'en' => 'Min (%d m²)',
            ],
            'max_with_unit' => [
                'nl' => 'Max (%d m²)',
                'fr' => 'Max (%d m²)',
                'en' => 'Max (%d m²)',
            ],
            'sort_by' => [
                'nl' => 'Sorteren op',
                'fr' => 'Trier par',
                'en' => 'Sort by',
            ],
            'sort_newest' => [
                'nl' => 'Nieuwste',
                'fr' => 'Plus récents',
                'en' => 'Newest',
            ],
            'sort_price_asc' => [
                'nl' => 'Prijs: laag naar hoog',
                'fr' => 'Prix : croissant',
                'en' => 'Price: Low to High',
            ],
            'sort_price_desc' => [
                'nl' => 'Prijs: hoog naar laag',
                'fr' => 'Prix : décroissant',
                'en' => 'Price: High to Low',
            ],
            'sort_area_desc' => [
                'nl' => 'Oppervlakte: groot naar klein',
                'fr' => 'Surface : décroissante',
                'en' => 'Surface: Large to Small',
            ],
            'reset_filters' => [
                'nl' => 'Filters wissen',
                'fr' => 'Réinitialiser les filtres',
                'en' => 'Reset filters',
            ],
            'apply_filters' => [
                'nl' => 'Filters toepassen',
                'fr' => 'Appliquer les filtres',
                'en' => 'Apply filters',
            ],
            'any' => [
                'nl' => 'Geen voorkeur',
                'fr' => 'Indifférent',
                'en' => 'Any',
            ],

            // Property Types standard dictionary
            'type_house' => [
                'nl' => 'Huis',
                'fr' => 'Maison',
                'en' => 'House',
            ],
            'type_apartment' => [
                'nl' => 'Appartement',
                'fr' => 'Appartement',
                'en' => 'Apartment',
            ],
            'type_villa' => [
                'nl' => 'Villa',
                'fr' => 'Villa',
                'en' => 'Villa',
            ],
            'type_office' => [
                'nl' => 'Kantoor',
                'fr' => 'Bureau',
                'en' => 'Office',
            ],
            'type_commercial' => [
                'nl' => 'Handelspand',
                'fr' => 'Commercial',
                'en' => 'Commercial',
            ],
            'type_land' => [
                'nl' => 'Grond',
                'fr' => 'Terrain',
                'en' => 'Land',
            ],
            'type_warehouse' => [
                'nl' => 'Magazijn',
                'fr' => 'Entrepôt',
                'en' => 'Warehouse',
            ],
            'type_property' => [
                'nl' => 'Pand',
                'fr' => 'Bien immobilier',
                'en' => 'Property',
            ],
        ];
    }

    /**
     * Translate static key into active language.
     *
     * @param string $key
     * @param string|null $lang Optional explicit language.
     * @return string
     */
    public static function trans( string $key, ?string $lang = null ): string {
        $dict = self::get_dictionary();
        $active_lang = ! empty( $lang ) ? strtolower( trim( $lang ) ) : self::get_current_language();

        if ( isset( $dict[ $key ][ $active_lang ] ) ) {
            return $dict[ $key ][ $active_lang ];
        }

        if ( isset( $dict[ $key ][ self::DEFAULT_LANGUAGE ] ) ) {
            return $dict[ $key ][ self::DEFAULT_LANGUAGE ];
        }

        if ( isset( $dict[ $key ]['en'] ) ) {
            return $dict[ $key ]['en'];
        }

        return $key;
    }

    /**
     * Translate status key to localized string.
     *
     * @param string $status
     * @param string|null $lang
     * @return string
     */
    public static function trans_status( string $status, ?string $lang = null ): string {
        $normalized = strtolower( trim( str_replace( '-', '_', $status ) ) );
        $key = 'status_' . $normalized;
        $translated = self::trans( $key, $lang );
        if ( $translated !== $key ) {
            return $translated;
        }
        return ucwords( str_replace( '_', ' ', $normalized ) );
    }

    /**
     * Translate property type ID or generic name to localized string.
     *
     * @param int|string $type
     * @param string|null $lang
     * @return string
     */
    public static function trans_type( $type, ?string $lang = null ): string {
        if ( is_numeric( $type ) ) {
            $type_id_map = [
                1  => 'type_house',
                2  => 'type_apartment',
                3  => 'type_villa',
                4  => 'type_office',
                5  => 'type_commercial',
                6  => 'type_land',
                22 => 'type_warehouse',
            ];
            $key = $type_id_map[ (int) $type ] ?? 'type_property';
            return self::trans( $key, $lang );
        }

        $type_str = strtolower( trim( (string) $type ) );
        $type_name_map = [
            'house'       => 'type_house',
            'maison'      => 'type_house',
            'huis'        => 'type_house',
            'apartment'   => 'type_apartment',
            'appartement' => 'type_apartment',
            'flat'        => 'type_apartment',
            'villa'       => 'type_villa',
            'office'      => 'type_office',
            'bureau'      => 'type_office',
            'kantoor'     => 'type_office',
            'commercial'  => 'type_commercial',
            'handelspand' => 'type_commercial',
            'land'        => 'type_land',
            'terrain'     => 'type_land',
            'grond'       => 'type_land',
            'bouwgrond'   => 'type_land',
            'warehouse'   => 'type_warehouse',
            'entrepot'    => 'type_warehouse',
            'entrepôt'    => 'type_warehouse',
            'magazijn'    => 'type_warehouse',
            'property'    => 'type_property',
            'pand'        => 'type_property',
            'bien'        => 'type_property',
        ];

        if ( isset( $type_name_map[ $type_str ] ) ) {
            return self::trans( $type_name_map[ $type_str ], $lang );
        }

        return (string) $type;
    }
}
