# GW2 WvW Tools
Command line tool for quick checking wvw score in Guild Wars 2

I created this lil script just for weekly wvw summary on [Tyria.in.th](https://tyria.in.th) <br/>
There will be more features coming in the future.

## Usage
```
$ php wvwscore.php
```

### View Score by Zone
add option --zone <zone>
```
$ php wvwscore.php --zone na
$ php wvwscore.php --zone eu
```

### View Score by Match
add option --match <match id>

get multiple matches by adding comma
```
$ php wvwscore.php --match 1-4
$ php wvwscore.php --match 1-3,1-4
```
_* This will override --zone option_

> Match ID consist with 2 value and concat with - <br/>
> first value represent server zone. which 1 = NA, 2 = EU <br/>
> second value represent match tier. <br/>
> So, if you want score of NA tier 4, match id will be 1-4 <br/>

### Template
Why on earth I need template for this thing? As I stated above (or not?), I created this lil tool for weekly report on my website. So I added template support to be able to generate ready-to-use html code for WordPress.

add option --template <filename>
```
$ php wvwscore.php --template template.html
```

This is very crude and stupid (but work for me) template engine. See template.html for usable tag.

## Planned
* Detailed info about maps and objectives
* Victory Scores (after Anet implemented it into API)
* Turn this thing into a PHAR app