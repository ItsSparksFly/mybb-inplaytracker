# Inplaytracker 3.0
Der <strong>Inplaytracker</strong> ist ein für Rollenspielzwecke in PHP und jscript entwickeltes Plugin, das mit der Forensoftware <em>MyBB 1.8</em> kompatibel ist. Diese Software wird unter der <a href="https://www.gnu.de/documents/lgpl-3.0.de.html" target="_blank">GNU LGPL V3-Lizenz</a> veröffentlicht. 

# Funktionsweise
Der <em>Inplaytracker</em> ermöglicht es Mitgliedern des Forums, andere Mitglieder bei Erstellen eines neuen Threads zu "taggen" - diese erhalten über das Plugin <a href="https://github.com/MyBBStuff/MyAlerts" target="_blank">MyAlerts</a> im Anschluss eine Benachrichtigung. Darüber hinaus lassen sich Spieldatum und Spielort, sowie eine kurze Beschreibung des Spielgeschehens für diesen Thread hinterlegen. Eine Übersicht aller getaggten Szenen liefert das Plugin ebenso mit wie einen nummerische Angabe aller offenen Szenen im Headerbereich des Forums.

Es kommt mit ein paar Funktionen, die ich euch hier stichpunktartig näher vorstellen möchte:

<ul>
<li> Einschränkung der Funktionen auf bestimmte Kategorien
<li> Taggen seiner Mitspieler
<li> "Inplay-Datum" zur Sortierung von Szenen
<li> Feld "Spielort" zur besseren Orientierung in Szenen
<li> Auflistung & Verlinkung der Mitspieler in der Themenübersicht
<li> MyAlert-Integration bei neu erstellter Szenen
<li> MyAlert-Integration bei neuer Antwort auf Inplayszene
<li> Übersicht aller aktiver Szenen mit ausstehenden Posts
<li>Verknüpfung über den Accountswitcher
<li> Festlegen einer Posting-Reihenfolge (wird bei Übersicht offener Posts beachtet)
<li> Anzeige der Gesamtanzahl (offener) Szenen im Header
<li> Szenentracker im Profil mit Zählung von Posts / sortiert nach Datum 
<li> Aufsplittung von archivierten und aktuellen Szenen im Profil
<li> Alle Mitspieler können Szenen-Informationen bearbeiten
</ul>

# Plugin funktionsfähig machen
<ul>
<li>Die Plugin-Datei ladet ihr in den angegebenen Ordner <b>inc/plugins</b> hoch.
<li>Die Language-Dateien ladet ihr in den entsprechenden Sprachordner.
<li>Das Plugin muss nun im Admin CP unter <b>Konfiguration - Plugins</b> installiert und aktiviert werden
<li>In den Foreneinstellungen findet ihr nun - ganz unten - Einstellungen zu "Inplaytracker". Macht dort eure gewünschten Einstellungen.
</ul>

Das Plugin ist nun einsatzbereit. Solltet ihr schon einiges an eurem Forum gemacht haben, und nicht wie ich im Testdurchlauf ein Default-Theme verwenden, kann es sein, dass nicht alle Variablen eingefügt werden. Sollte euch eine Anzeige fehlen, könnt ihr auf folgende Variablen zurückgreifen:

<blockquote>{$header_inplaytracker}  // Link zur Übersicht der Szenen (header)
* ruft ipt_header auf

{$member_profile_inplaytracker} // Szenentracker im Profil (member_profile)
* ruft ipt_member_profile auf

{$newthread_inplaytracker} // Eingabefeld für Postingpartner (newthread)
* ruft ipt_newthread auf

{$editpost_inplaytracker} // Eingabefeld für Postingpartner (newthread)
* ruft ipt_newthread auf

{$post['inplaytracker']} // Szenen-Informationen überm Post (postbit, postbit_classic)
* ruft ipt_postbit auf

{$showthread_inplaytracker} // Szenen-Informationen überm Post (showthread)
* ruft ipt_showthread auf</blockquote>

# Template-Änderungen
Folgende Templates werden durch dieses Plugin <i>neu hinzugefügt</i>:

<ul>
<li>ipt_editscene
<li>ipt_header
<li>ipt_member_profile
<li>ipt_member_profile_bit
<li>ipt_member_profile_bit_user
<li>ipt_misc
<li>ipt_misc_bit 
<li>ipt_misc_bit_scene 
<li>ipt_newthread 
<li>ipt_postbit
<li>ipt_showthread
</ul>

Folgende Templates werden durch dieses Plugin <i>bearbeitet</i>:
<ul>
<li>header
<li>member_profile
<li>newthread
<li>editpost
<li>postbit
<li>postbit_classic
</ul>


# Changelog 
<strong>2.0 => 3.0 (latest)</strong>

- <strong>Optimierung von Quellcode & Datenbank</strong> &bull; sowohl Quellcode als auch Datenbank wurden an aktuelle Standards angepasst und sind in Zukunft auch bei größeren Datenmengen performanter. Plugins und Erweiterungen, die für die Vorgängerversion des Plugins entwickelt wurden, werden mit hoher Wahrscheinlichkeit <em>nicht mehr kompatibel</em> sein und eine Überarbeitung brauchen.
- <strong>Entfallene Funktionen</strong> &bull; User können nicht weiter eine zu bespielende Tageszeit angeben, auch die Möglichkeit, Szenen als privat oder öffentlich zu markieren entfällt. Admins haben nicht länger die Möglichkeit, eine eigene Zeitrechnung zu implementieren. Fantasyboards wird empfohlen, weiter auf die Version 2.0 zuzugreifen.
- <strong>Neue Funktionen:</strong> &bull; Auswahl mehrerer Inplaykategorien, Bearbeitung von Szenendaten auch durch Postpartner, Verknüpfung von Accounts über den Accountswitcher, Angabe einer Szenenkurzbeschreibung bei Erstellen eines neuen Threads, zugänglichere Handhabung der Angabe des Datums, Trennung von archivierten und aktuellen Szenen im Profil



# Voraussetzungen
<a href="http://doylecc.altervista.org/bb/downloads.php?dlid=4&cat=1" target="_blank">Enhanced Account Switcher</a> von doylecc<br />

# Empfohlene Pugins
<a href="https://github.com/MyBBStuff/MyAlerts" target="_blank">MyAlerts</a> von euanT<br />
<a href="https://github.com/aheartforspinach/Posting-Erinnerung" target="_blank">Posting Erinnerung</a> von aheartforspinach<br />
<a href="https://github.com/aheartforspinach/Archivierung">Archivierung</a> von aheartforspinach

# Upgrade
Falls die Version <strong>2.0</strong> (latest release) installiert ist, wird folgendes Upgrade-Vorgehen unbedingt empfohlen:

- "Inplaytracker 2.0" im AdminCP deaktivieren (NICHT deinstallieren!) => alte Templates werden gelöscht
- Inplaytracker 3.0 hochladen
- Inplaytracker 3.0 installieren & aktivieren
- Einstellungen unter <b>Konfiguration -> Einstellungen -> Inplaytracker (2 Einstellungen)</b> vornehmen. 
- misc.php?action=do_upgrade aufrufen => die aktuellen Inplay-Daten werden an die neuen Datenbankstrukturen angepasst, alte Einstellungen und Datenbankänderungen werden gelöscht
- Kontrollieren ob alles funktioniert hat
- Inplaytracker 2.0 anschließend deinstallieren
