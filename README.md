# static-backend
PHP ReST-API auf File Basis für den Static Bereich

Für die Verwaltung eines öffentlichen Static-Bereiches wird die Struktur des Bereiches ggf. in JSON dargestellt.
Sie kann über ReST-API abgefragt und verändert werden. Die API muss daher folgende REST Methoden bereit stellen. 



## Methoden

`GET /resources`

Gibt als HTTP-Response die komplette Verzeichnisstruktur als JSON und als Status-Code 200 zurück. 
Siehe Struktur für das entsprechende JSON.
Falls einzelne Verzeichnisse oder Dateien nicht lesbar sind, so werden sie nicht in der JSON aufgeführt.
Sollten grundlegendere Fehler auftreten, so wird nichts zurückgegeben und der Status-Code der Response ist 500.


`GET / resource`

Gibt als HTTP-Response die angefragte Resource und einen Status-Code 200 zurück.
Siehe Resource für die Struktur der JSON, welche als Body der HTTP-Response zurückgegeben wird.
Falls die Resource nicht abgerufen werden kann, wird eine JSON der Struktur Error mit Fehlerdaten und ein Status-Code 400 oder 500 zurück gegeben.


`POST / resource`

Gibt als HTTP-Response nichts außer einem Status-Code 201 zurück.
Siehe Resource für die Struktur der JSON, welche als Content des HTTP-Request erlaubt wird.
Falls die Resource nicht angelegt werden kann, wird eine JSON der Struktur Error mit Fehlerdaten und ein Status-Code 400 oder 500 zurück gegeben.


`UPDATE / resource`

Gibt als HTTP-Response nichts außer einem Status-Code 201 zurück.
Siehe Resource für die Struktur der JSON, welche als Content des HTTP-Request erlaubt wird.
Falls die Resource nicht geändert werden kann, wird eine JSON der Struktur Error mit Fehlerdaten und ein Status-Code 400 oder 500 zurück gegeben.


`DELETE / resource`

Gibt als HTTP-Response nichts außer einem Status-Code 201 zurück.
Siehe Resource für die Struktur der JSON, welche als Content des HTTP-Request erlaubt wird.
Falls die Resource nicht gelöscht werden kann, wird eine JSON der Struktur Error mit Fehlerdaten und einen Status-Code 400 oder 500 zurück gegeben.



## Struktur
Wichtig! Enthält keine Files oder Verzeichnisse, die nicht zum Static gehören und dazu dienen den Baum aufzubauen!

Datei-Baum:
```
{
  "name": "",
  "path": "",
  "type": "dir",
  "children": [
    {
      "name": "somefile.dat",
      "path": "/somefile.dat",
      "type": "file"
      "
    },
    {
      "name": "somedir",
      "path": "/somedir",
      "type": "dir",
      "children": [
        {
          "name": "somesubdir"
          "path": "/somedir/somesubdir"
          "type": "dir",
          "children": []
        },
        {
          "name": "anotherfile.type",
          "path": "/somedir/anotherfile.type",
          "type": "file"
        }
      ]
    },
  ]
}
```

Resource:
```
{
  "name": <String> "Name der Datei inklusive Datei-Endung",
  "path": <String> "Kompletter String inklusive name-Attribut, Verzeichnis-Separator: '/'"
}
```
Error:
```
{
  "message": <String> "Fehlerbeschreibung",
  "trace": <String> (optional) "StackTrace",
  "code": <UInt> Fehlercode
}
```
