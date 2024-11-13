# NS Interational WP plugin

Via deze plugin worden er 3 nsi modules beschikbaar gesteld binnen jouw WP omgeving, deze plugins zijn te gebruiken via de WP shortcodes.

## Module instellingen

Voor deze plugin heb je een aantal instellingen nodig om het goed te laten werken, als de plugin is geactiveerd zal een menu optie beschikbaar worden in het admin portaal. Het menu item zal onderaan aanwezig zijn met de tekst `NS International`.

### De instellingen

Er zijn twee instellingen die nu te configureren zijn, waarbij de `Tracking code` verplicht is en de `Default vertrek station code` optioneel.

#### Tracking code

Dit is de code van jou tracking platform, dit wordt via de module gebruikt bij het genereren van de URL's naar NSI. Zonder deze tracking code zullen de modules niet goed werken.

#### Default vertrek station code

Dit is een handigheid om niet altijd een vertrek station in te hoeven vullen. Je kan hier een station code invullen, deze code zal dan altijd worden gebruikt als er geen specifieke vertrek station is opgegeven in de shortcodes.

### Shortcode generator

Om te helpen bij het maken van de short codes is er een shortcode generator toegevoegd. Je kan hier zoeken naar stations om te selecteren voor jouw shortcode.

- Linker muis klik is voor `vertek`
- Rechter muis klik is voor `Bestemming`

Er is ook een selectbox waarmee je het type module kan selecteren. Vervolgens komt er helemaal rechts een shortcode te staan met jouw keuzes, klik op de shortcode om deze te kopieeren.

## Zoek formulier

De zoekformulier module is bedoeld om de gebruiker via een snel te gebruiken formulier te laten zoeken naar trein reizen. Het formulier bestaat uit 3 invoervelden en 1 zoek knop.

### Station zoek velden

Bij het formulier zitten 2 station zoek velden, deze werken door er in te typen. Vervolgens zal er een autocomplete worden uitgevoerd die een lijst met mogelijke matches toont waaruit de gebruiker kan kiezen.

Dit veld komt ook voor bij andere modules.

### Datum selecter

Bij het datum invoerveld wordt er een datepicker getoond, deze is onderdeel van de [jQuery UI](https://jqueryui.com/). De gebruiker kan hier een datum selecteren om op te zoeken.

### Zoeken

Als de gebruiker op de zoek knop klikt, wordt er aan de achterkant in javascript een URL samengesteld met de trackingcode en ingevulde waardes. De url zal je vervolgens in een nieuw venster doorsturen naar de NSI website.

### Short code

Hieronder de short code en mogelijke argumenten die je kan meesturen.

**Shortcode**
`[ns-international-search /]`

**Argumenten**

- **from:** De code van het vertrek station
- **to:** De code van het bestemming station

Voorbeeld:
`[ns-international-search from="NLASD" to="FRPAR" /]`
