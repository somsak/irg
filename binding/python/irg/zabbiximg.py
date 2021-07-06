#!/usr/bin/env python3

import os, sys, urllib.parse, urllib.request, urllib.parse, urllib.error, time

import pycurl

class ZabbixImg(object) :

    def __init__(self, zbx_base_url, user, password, verbose = False, version = 3) :
        self.zbx_base_url = zbx_base_url
        if not self.zbx_base_url.endswith('/') :
            self.zbx_base_url += '/'
        self.user = user
        self.password = password
        self.zabbix_version = version

        self.curl = pycurl.Curl()
        self.curl.setopt(pycurl.COOKIEFILE, "")
        self.curl.setopt(pycurl.VERBOSE, verbose)

        # Log-in
        self.curl.setopt(pycurl.URL, urllib.parse.urljoin(self.zbx_base_url, "index.php"))
        self.curl.setopt(pycurl.POST, 1)
        form_data = [
                    ("request", ""),
                    ("name", urllib.parse.quote(self.user)),
                    ("password", urllib.parse.quote(self.password)),
                    ("autologin", "1"),
                    ("enter", "Sign in"),
                ]
        self.curl.setopt(pycurl.HTTPPOST, form_data)
        # post_data = {
        #              "request": "",
        #              "name": urllib.quote(self.user),
        #              "password": urllib.quote(self.password),
        #              "autologin": "1",
        #              "enter": "Sign in",
        # }
        # self.curl.setopt(pycurl.POSTFIELDS, urllib.urlencode(post_data))
        self.curl.perform()
        self.curl.setopt(pycurl.POST, 0)

    def fetch_img(self, graphid, start_time, end_time, output, width = 600, height = 100) :
        '''
        Write graph image to output file

        @param graphid Zabbix graph id
        @param start_time start time expressed in second since epoch, as returned from time.localtime()
        @param end_time end time
        @param output output file-like object
        @param width width of the graph
        @param height height of the graph
        '''

        if self.zabbix_version < 4 :
            # Zabbix is older than 3.2
            period = end_time - start_time
            start_time_str = time.strftime("%Y%m%d%H%M%S", time.localtime(start_time))
            data = urllib.parse.urlencode(
                    {
                        'graphid': str(graphid),
                        'stime': start_time_str,
                        'period': str(period),
                        'width': str(width),
                        'height': str(height),
                        'isNow': '0',
                    }
            )
            l = list(urllib.parse.urlparse( urllib.parse.urljoin(self.zbx_base_url, 'chart2.php')))
            l[4] = data
        else :
            # Newer zabbix
            start_time_str = time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(start_time))
            end_time_str = time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(end_time))
            data = urllib.parse.urlencode(
                    {
                        'graphid': str(graphid),
                        'from': start_time_str,
                        'to': end_time_str,
                        'profileIdx': 'web.graphs.filter',
                        'width': str(width),
                        'height': str(height),
                    }
            )
            l = list(urllib.parse.urlparse( urllib.parse.urljoin(self.zbx_base_url, 'chart2.php')))
            l[4] = data
        url = urllib.parse.urlunparse(l)
        #print('Fetching URL = ' + url)
        #self.curl.setopt(pycurl.VERBOSE, True)
        self.curl.setopt(pycurl.URL, url)
        self.curl.setopt(pycurl.WRITEDATA, output)
        self.curl.perform()

if __name__ == '__main__' :
    import time

    zbximg = ZabbixImg('https://www.test.com/zabbi/', 'report', '')
    start = int(time.mktime((2016, 1, 1, 0, 0, 0, 0, 0, 0)))
    end = int(time.mktime((2016, 1, 31, 23, 59, 60, 0, 0, 0)))
    print(start, time.ctime(start))
    print(end, time.ctime(end))
    zbximg.fetch_img(2029, start, end, open('output.png', 'wb'))
