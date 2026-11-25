Psmb.ru Distribution
========

## Installation

Check out [our contributor page](https://psmb.github.io) for guides how to install this project and more.

## Sermon upload skill

The [`psmb-sermon-upload` skill](skills/psmb-sermon-upload/SKILL.md) guides an agent through drafting, reviewing, and publishing sermons in Neos.

To install it, open a terminal in your PsmbDistr checkout. Replace the example path with your agent's configured skills directory, then copy the whole folder so the skill can find its reference files.

```sh
agent_skills_dir="/path/to/your/agent/skills"
mkdir -p "$agent_skills_dir"
cp -R skills/psmb-sermon-upload "$agent_skills_dir/"
```

Start a new agent session and ask it to use `psmb-sermon-upload` with your sermon document. If your agent supports loading instructions from a file, you can also point it directly to `skills/psmb-sermon-upload/SKILL.md` in this checkout. Use your own authorized Neos session or provide credentials securely when signing in.
