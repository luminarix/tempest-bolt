import { useEffect, useRef } from 'react'
import { Compartment, EditorState, type Extension } from '@codemirror/state'
import { EditorView, keymap, lineNumbers, highlightActiveLine, placeholder } from '@codemirror/view'
import { defaultKeymap, history, historyKeymap, indentWithTab } from '@codemirror/commands'
import { defaultHighlightStyle, syntaxHighlighting } from '@codemirror/language'
import { php } from '@codemirror/lang-php'
import { oneDark } from '@codemirror/theme-one-dark'

function themeExtension(dark: boolean): Extension {
  return dark ? oneDark : syntaxHighlighting(defaultHighlightStyle)
}

interface EditorProps {
  initialValue: string
  dark: boolean
  onChange: (value: string) => void
  onSubmit: () => void
}

export function Editor({ initialValue, dark, onChange, onSubmit }: EditorProps) {
  const parent = useRef<HTMLDivElement>(null)
  const view = useRef<EditorView | null>(null)
  const theme = useRef(new Compartment())

  const onChangeRef = useRef(onChange)
  const onSubmitRef = useRef(onSubmit)
  const darkRef = useRef(dark)
  onChangeRef.current = onChange
  onSubmitRef.current = onSubmit
  darkRef.current = dark

  useEffect(() => {
    if (!parent.current) {
      return
    }

    const state = EditorState.create({
      doc: initialValue,
      extensions: [
        lineNumbers(),
        highlightActiveLine(),
        placeholder('Write PHP — ⌘/Ctrl + Enter to run'),
        history(),
        php({ plain: true }),
        theme.current.of(themeExtension(darkRef.current)),
        keymap.of([
          {
            key: 'Mod-Enter',
            preventDefault: true,
            run: () => {
              onSubmitRef.current()
              return true
            },
          },
          indentWithTab,
          ...defaultKeymap,
          ...historyKeymap,
        ]),
        EditorView.updateListener.of((update) => {
          if (update.docChanged) {
            onChangeRef.current(update.state.doc.toString())
          }
        }),
        EditorView.theme({
          '&': { height: '100%', fontSize: '14px', backgroundColor: 'transparent' },
          '.cm-gutters': { backgroundColor: 'transparent', border: 'none' },
          '.cm-activeLine': { backgroundColor: 'color-mix(in srgb, currentColor 4%, transparent)' },
          '.cm-activeLineGutter': { backgroundColor: 'transparent' },
          '.cm-scroller': {
            fontFamily:
              'ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, monospace',
          },
          '&.cm-focused': { outline: 'none' },
        }),
      ],
    })

    const editor = new EditorView({ state, parent: parent.current })
    view.current = editor
    editor.focus()

    return () => {
      editor.destroy()
      view.current = null
    }
  }, [])

  useEffect(() => {
    view.current?.dispatch({ effects: theme.current.reconfigure(themeExtension(dark)) })
  }, [dark])

  return <div ref={parent} className="h-full w-full overflow-hidden" />
}
