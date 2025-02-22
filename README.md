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
Returns the avatar (face) of the give user.

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
