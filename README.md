# Skin API

Give your players the ability to change their skin and/or cape.

## AzLink with SkinsRestorer

The skin can automatically be applied in-game, when using AzLink with
[SkinsRestorer](https://skinsrestorer.net/).

In the `config.yml` of AzLink, set `skinrestorer-integration` to `true`.

## Endpoints

All endpoints can optionally end with `.png`.

### Skin

**GET** `/api/skin-api/skins/{user_id|user_name}`
Returns the skin layout of the given user.

**GET** `/api/skin-api/avatars/face/{user_id|user_name}`
Returns the avatar (face) of the given user.

**GET** `/api/skin-api/avatars/body/{user_id|user_name}`
Returns the body of the given user.

**GET** `/api/skin-api/avatars/combo/{user_id|user_name}`
Returns the avatar, with the skin body on top, of the given user.

**POST** `/api/skin-api/skins`
| Parameter      | Type      | Description             |
| -------------- | --------- | ----------------------- |
| `access_token` | string    | The user's access token |
| `skin`         | image/png | The skin layout         |

### Cape

Capes must be enabled in the Admin Dashboard in order to be in use.

**GET** `/api/skin-api/capes/{user_id|user_name}`
Returns the cape of the given user.

**POST** `/api/skin-api/capes`
| Parameter      | Type      | Description             |
| -------------- | --------- | ----------------------- |
| `access_token` | string    | The user's access token |
| `cape`         | image/png | The cape file           |

### Profile as JSON

**GET** `/api/skin-api/profile/{user_name}`

Returns the JSON information for skin and cape (if present):
```json
{
  "username": "Notch",
  "skin": {
    "url": "https://example.tld/api/skin-api/skins/Notch.png",
    "hash": "sha256:8af144fa76c3d1c406a29bd6d187f9ceaf1522207b3de259a92b08abbc826762",
    "slim": true,
    "default": false,
    "last_modified": "2010-05-06T12:50:57+00:00"
  },
  "cape": null
}
```
