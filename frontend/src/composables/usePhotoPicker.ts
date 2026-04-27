import { Capacitor } from '@capacitor/core'

/**
 * Platform-agnostic photo picker.
 *
 * On a native Capacitor runtime, uses @capacitor/camera with either the
 * device camera or the photo library. On the web, falls back to a hidden
 * <input type="file" accept="image/*"> element.
 *
 * Always resolves to a Blob suitable for FormData upload.
 */
export type PhotoSource = 'camera' | 'library'

export function usePhotoPicker() {
  const isNative = Capacitor.isNativePlatform()

  async function pick(source: PhotoSource = 'library'): Promise<Blob | null> {
    if (isNative) {
      return pickFromCapacitor(source)
    }
    return pickFromWeb()
  }

  return { isNative, pick }
}

async function pickFromCapacitor(source: PhotoSource): Promise<Blob | null> {
  const { Camera, CameraResultType, CameraSource } = await import('@capacitor/camera')

  try {
    const photo = await Camera.getPhoto({
      quality: 85,
      allowEditing: false,
      resultType: CameraResultType.Base64,
      source: source === 'camera' ? CameraSource.Camera : CameraSource.Photos,
    })
    if (!photo.base64String) return null
    const mime = `image/${photo.format ?? 'jpeg'}`
    return base64ToBlob(photo.base64String, mime)
  } catch (err) {
    if (isCameraCancellation(err)) {
      return null
    }
    throw err
  }
}

function pickFromWeb(): Promise<Blob | null> {
  return new Promise((resolve) => {
    const input = document.createElement('input')
    input.type = 'file'
    input.accept = 'image/*'
    input.onchange = () => {
      const file = input.files?.[0] ?? null
      resolve(file)
    }
    input.oncancel = () => resolve(null)
    input.click()
  })
}

function base64ToBlob(b64: string, mime: string): Blob {
  const binary = atob(b64)
  const len = binary.length
  const bytes = new Uint8Array(len)
  for (let i = 0; i < len; i++) {
    bytes[i] = binary.charCodeAt(i)
  }
  return new Blob([bytes], { type: mime })
}

function isCameraCancellation(err: unknown): boolean {
  const msg = (err as { message?: string })?.message?.toLowerCase() ?? ''
  return msg.includes('cancel') || msg.includes('user denied')
}
