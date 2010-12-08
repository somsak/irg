#!/usr/bin/python

import calendar
import time
import urllib
import urllib2
from urllib2 import urlopen as _urlopen
import struct
import re

import simplejson

warning_cre = re.compile(r'<br />.*<br />\n', re.S)

opener = urllib2.build_opener(urllib2.HTTPCookieProcessor())
urllib2.install_opener(opener)

def urlopen(*args):
    return _urlopen(*args)

def get_image_info(data):
    size = len(data)
    height = -1
    width = -1
    content_type = ''

    if ((size >= 24) and (data[:8] == '\211PNG\r\n\032\n')
          and (data[12:16] == 'IHDR')):
        content_type = 'image/png'
        w, h = struct.unpack(">LL", data[16:24])
        width = int(w)
        height = int(h)

    # Maybe this is for an older PNG version.
    elif (size >= 16) and (data[:8] == '\211PNG\r\n\032\n'):
        # Check to see if we have the right content type
        content_type = 'image/png'
        w, h = struct.unpack(">LL", data[8:16])
        width = int(w)
        height = int(h)

    return content_type, width, height

def get_param_monthly(year, month):
    day, last = calendar.monthrange(year, month)
    start = int(time.mktime((year, month, 1, 0, 0, 0, 0, 1, 0)))
    end = int(time.mktime((year, month, last, 23, 59, 60, 0, 1, 0)))
    return end-start, start, end

def get_param_range(start, end):
    start = int(start)
    end = int(end)
    return end-start, start, end

class IRG:
    def __init__(self, url, user, passwd, verbose=True):
        self._verbose = verbose
        self.cacti_url = url+'index.php'
        self.graph_url = url+'graph_image.php'
        self.repoti_url = url+'plugins/repoti/repoti.php'
        self.login(user, passwd)

    def verbose(self, msg):
        if self._verbose:
            print msg

    def login(self, user, passwd):
        data = {'action': 'login',
                'login_username': user,
                'login_password': passwd}
        fd = urlopen(self.cacti_url, urllib.urlencode(data))
        fd.read()

    def get_rras(self):
        data = {'c': 'rras',
                'a': 'get'}
        fd = urlopen(self.repoti_url+'?'+urllib.urlencode(data))
        json = fd.read()
        return simplejson.loads(json)

    def get_rra_by_id(self, id):
        data = {'c': 'rras',
                'a': 'get',
                'id': str(id)}
        fd = urlopen(self.repoti_url+'?'+urllib.urlencode(data))
        json = fd.read()
        return simplejson.loads(json)

    def get_hosts(self):
        data = {'c': 'hosts',
                'a': 'get'}
        fd = urlopen(self.repoti_url+'?'+urllib.urlencode(data))
        json = fd.read()
        return simplejson.loads(json)

    def get_graphs_by_host(self, host_id):
        data = {'c': 'graphs',
                'a': 'getByHostId',
                'hostId': str(host_id)}
        fd = urlopen(self.repoti_url+'?'+urllib.urlencode(data))
        json = fd.read()
        return simplejson.loads(json)

    def get_reports(self):
        data = {'c': 'reports',
                'a': 'get'}
        fd = urlopen(self.repoti_url+'?'+urllib.urlencode(data))
        json = fd.read()
        reports = []
        for report in simplejson.loads(json):
            report['graph_ids'] = report['graph_ids'].split(',')
            reports.append(report)
        return reports

    def get_report_by_name(self, name):
        for report in self.get_reports():
            if report['template_name'] == name:
                return report

    def get_stat(self, graph_id, rra_id, timespan,
                 start_time, end_time, start_prime, end_prime):
        data = {'c': 'graphs',
                'a': 'getstat',
                'graphId': str(graph_id),
                'rraTypeId': str(rra_id),
                'timespan': str(timespan),
                'graphStart': str(start_time),
                'graphEnd': str(end_time),
                'beginPrime': start_prime,
                'endPrime': end_prime}
        fd = urlopen(self.repoti_url+'?'+urllib.urlencode(data))
        json = fd.read()
        json = warning_cre.sub('', json)
        try:
            o = simplejson.loads(json)
        except Exception, why:
            print self.repoti_url+'?'+urllib.urlencode(data)
            print json
            o = None
        return o

    def get_graph_image(self, graph_id, rra_id, start_time, end_time):
        data = {'local_graph_id': str(graph_id),
                'rra_id': str(rra_id),
                'graph_start': str(start_time),
                'graph_end': str(end_time)}
        fd = urlopen(self.graph_url+'?'+urllib.urlencode(data))
        content = fd.read()
        return content

if __name__ == '__main__':
    irg = IRG('http://127.0.0.1/cacti/', 'admin', 'xxx')
    print irg.get_rras()
    print irg.get_rras(3)
    print irg.get_hosts()
    print irg.get_graphs_by_host(2)
    print irg.get_reports()
    print irg.get_stat(132, 3, 2678400, 1288717200, 1291395600, '08:00', '12:00')
    #print irg.get_graph_image(132, 3, 1288717200, 1291395600)
    print get_param_monthly(2010, 11)
