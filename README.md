# stocks

The index.php file assumes the necessary keys for alphavantage.co and newsapi.org 
are saved in a file keys.php somewhere outside your public_html folder. 

jQRangeSlider-5.7.2 is assumed to be saved in working directory as well.

Swap the iThing.css file native to jQRangeSlider-5.7.2 with the one in this repo
or simply alter the css import link on line 84 of index.php to point to wherever
you wish to save the iThing.css file from this repo. 

------------------------------------------------------------------------------------

A working sample of this web app can be found at https://collaber.org/stock/ . 
Leverages Rest API's from alphavantage.co and newsapi.org to fetch stock prices
and news articles for the company searched.

Use:
  1. Enter a company tag into the search bar to view stock valuations of that company. 
  2. Move sliders on graph and click "Search News" to view news articles relevant to 
     the selected company in the selected time range. Both the position and the span 
     of the time range can be selected by manipulating the middle and edges of the 
     green area respectively. 
     
Purpose:
  Simply put, I wanted to be able to view historical news for a company alongside
  historical stock valuations of that company. I was loosely inspired by sound cloud's
  commenting feature for songs and how users can insert comments at specific times
  throughout a song. This app simply displays a graph of stock prices for a given
  company and treats news articles about that company as comments in this sound cloud 
  model.

Security (Mixed content / cross domain):
  Note, this web page is pulling from Rest API's from two different sources and also 
  embedding links and images referenced again on those API's. Properly designed, 
  this data would be saved in a DB on the same domain as the site after having been
  cleaned/hashed/whatever. However, the terms of service from both API providers
  restricts usage so that this cannot be done. And because this is the equivelent 
  of a shower thought project I whipped up in a night, making it robust is outside the
  scope of intended use. 
