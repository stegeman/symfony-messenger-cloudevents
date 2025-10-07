# Cloudevents integation for Symfony Messenger

## Install

```composer require stegeman/symfony-messenger-cloud-events```

Enable the bundle in your kernel:

_bundles.php_
```
return [
    ...
    Stegeman\Messenger\CloudEvents\CloudEventsBundle::class => ['all' => true],
    ...
];
```
## Configuration

Configure the correct serializer for your transport:

```
transports:
    async:
        ...
        serializer: 'Stegeman\Messenger\CloudEvents\Serializer\CloudEventsSerializer'
    ...
```

To prevent your messages will be mapped to a PHP namespace each message must be named and matched with a message. This 
can be done by adding name -> class mapping to your configuration (config/packages/cloud_events.yaml) :
```
cloud_events:
  registry:
    - name: "test-event"
      className: "App\\Domain\\Event\\TestEvent"
```

Now it is known which name to map to which class. This is necessary for both serializing as deserializing.

This is all that is needed to get started.

All set, go!

## Override services

It is possible to override the normalizer service. This can be done by adding the following to your configuration (config/packages/cloud_events.yaml):

```
    cloud_events:
      ...
      normalizer_service_id: name.of.your.own.normalizer.service
```