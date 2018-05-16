<?php
use PHPUnit\Framework\TestCase;

class ReadEventsTest extends TestCase
{
    public function testReadEventsSinglePageSingleEvent()
    {
        $events_page = '{
          "pages": {
            "current": 1,
            "total": 1
          },
          "events": [
            {
              "calendar_id": "cal_U9uuErStTG@EAAAB_IsAsykA2DBTWqQTf-f0kJw",
              "event_uid": "evt_external_54008b1a4a41730f8d5c6037",
              "summary": "Company Retreat"
            }
          ]
        }';

        $http = $this->createMock('HttpRequest');
        $http->expects($this->once())
            ->method('get_page')
            ->with(
                $this->equalTo('https://api.cronofy.com/v1/events'),
                $this->equalTo(array(
                    'Authorization: Bearer accessToken',
                    'Host: api.cronofy.com'
                )),
                "?tzid=Etc%2FUTC"
            )
            ->will($this->returnValue(array($events_page, 200)));

        $cronofy = new Cronofy(array(
            "client_id" => "clientId",
            "client_secret" => "clientSecret",
            "access_token" => "accessToken",
            "refresh_token" => "refreshToken",
            "http_client" => $http,
        ));

        $params = array(
            'tzid' => 'Etc/UTC'
        );

        $actual = $cronofy->read_events($params);
        $this->assertNotNull($actual);
        $this->assertCount(1, $actual);
        $this->assertCount(1, $actual->each());

        foreach ($actual->each() as $event) {
            $this->assertNotNull($event);
            $this->assertEquals($event["event_uid"], "evt_external_54008b1a4a41730f8d5c6037");
        }

        foreach ($actual as $event) {
            $this->assertNotNull($event);
            $this->assertEquals($event["event_uid"], "evt_external_54008b1a4a41730f8d5c6037");
        }
    }

    public function testReadEventsTwoPageSingleEvent()
    {
        $page_1 = '{
          "pages": {
            "current": 1,
            "total": 2,
            "next_page": "https://api.cronofy.com/v1/events/pages/08a07b034306679e"
          },
          "events": [
            {
              "calendar_id": "cal_U9uuErStTG@EAAAB_IsAsykA2DBTWqQTf-f0kJw",
              "event_uid": "evt_external_event_one",
              "summary": "Company Retreat"
            }
          ]
        }';

        $page_2 = '{
          "pages": {
            "current": 2,
            "total": 2
          },
          "events": [
            {
              "calendar_id": "cal_U9uuErStTG@EAAAB_IsAsykA2DBTWqQTf-f0kJw",
              "event_uid": "evt_external_event_two",
              "summary": "Company Retreat"
            }
          ]
        }';

        $http = $this->createMock('HttpRequest');
        $http->expects($this->exactly(2))
            ->method('get_page')
            ->will($this->onConsecutiveCalls(array($page_1, 200), array($page_2, 200)));

        $cronofy = new Cronofy(array(
            "client_id" => "clientId",
            "client_secret" => "clientSecret",
            "access_token" => "accessToken",
            "refresh_token" => "refreshToken",
            "http_client" => $http,
        ));

        $params = array(
            'tzid' => 'Etc/UTC'
        );

        $actual = $cronofy->read_events($params);
        $this->assertNotNull($actual);
        $this->assertCount(2, $actual);
    }

    public function testReadEventsCanBeConvertedToArray()
    {
        $page_1 = '{
          "pages": {
            "current": 1,
            "total": 2,
            "next_page": "https://api.cronofy.com/v1/events/pages/08a07b034306679e"
          },
          "events": [
            {
              "calendar_id": "cal_U9uuErStTG@EAAAB_IsAsykA2DBTWqQTf-f0kJw",
              "event_uid": "evt_external_event_one",
              "summary": "Company Retreat"
            }
          ]
        }';

        $page_2 = '{
          "pages": {
            "current": 2,
            "total": 2
          },
          "events": [
            {
              "calendar_id": "cal_U9uuErStTG@EAAAB_IsAsykA2DBTWqQTf-f0kJw",
              "event_uid": "evt_external_event_two",
              "summary": "Company Retreat"
            }
          ]
        }';

        $http = $this->createMock('HttpRequest');
        $http->expects($this->exactly(2))
            ->method('get_page')
            ->will($this->onConsecutiveCalls(array($page_1, 200), array($page_2, 200)));

        $cronofy = new Cronofy(array(
            "client_id" => "clientId",
            "client_secret" => "clientSecret",
            "access_token" => "accessToken",
            "refresh_token" => "refreshToken",
            "http_client" => $http,
        ));

        $params = array(
            'tzid' => 'Etc/UTC'
        );

        $actual = $cronofy->read_events($params);
        $event_uids = array_map(function (array $event) {
            return $event['event_uid'];
        }, iterator_to_array($actual));

        $this->assertContains('evt_external_event_one', $event_uids);
        $this->assertContains('evt_external_event_two', $event_uids);
    }
}
